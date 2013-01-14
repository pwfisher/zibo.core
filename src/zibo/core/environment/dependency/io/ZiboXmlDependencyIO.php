<?php

namespace zibo\core\environment\dependency\io;

use zibo\library\dependency\exception\DependencyException;

use zibo\core\environment\filebrowser\FileBrowser;
use zibo\core\Zibo;

use zibo\library\dependency\io\DependencyIO;
use zibo\library\dependency\Dependency;
use zibo\library\dependency\DependencyCall;
use zibo\library\dependency\DependencyCallArgument;
use zibo\library\dependency\DependencyContainer;
use zibo\library\filesystem\File;
use zibo\library\xml\dom\Document;

use \DOMElement;

/**
 * Implementation to get a dependency container based on XML files
 */
class ZiboXmlDependencyIO implements DependencyIO {

    /**
     * The file name
     * @var string
     */
    const FILE = 'dependencies.xml';

    /**
     * Name of the dependency tag
     * @var string
     */
    const TAG_DEPENDENCY = 'dependency';

    /**
     * Name of the call tag
     * @var string
     */
    const TAG_CALL = 'call';

    /**
     * Name of the argument tag
     * @var string
     */
    const TAG_ARGUMENT = 'argument';

    /**
     * Name of the property tag
     * @var string
     */
    const TAG_PROPERTY = 'property';

    /**
     * Name of the interface attribute
     * @var string
     */
    const ATTRIBUTE_INTERFACE = 'interface';

    /**
     * Name of the class attribute
     * @var string
     */
    const ATTRIBUTE_CLASS = 'class';

    /**
     * Name of the extends attribute
     * @var string
     */
    const ATTRIBUTE_EXTENDS = 'extends';

    /**
     * Name of the id attribute
     * @var string
     */
    const ATTRIBUTE_ID = 'id';

    /**
     * Name of the method attribute
     * @var string
     */
    const ATTRIBUTE_METHOD = 'method';

    /**
     * Name of the name attribute
     * @var string
     */
    const ATTRIBUTE_NAME = 'name';

    /**
     * Name of the type attribute
     * @var string
     */
    const ATTRIBUTE_TYPE = 'type';

    /**
     * Name of the value attribute
     * @var string
     */
    const ATTRIBUTE_VALUE = 'value';

    /**
     * Instance of the file browser
     * @var zibo\core\environment\filebrowser\FileBrowser
     */
    private $fileBrowser;

    /**
     * Name of the environment
     * @var string
     */
    private $environment;

    /**
     * Constructs a new XML dependency IO
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @param string $environment
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, $environment = null) {
        $this->fileBrowser = $fileBrowser;
        $this->environment = $environment;
    }

    /**
     * Gets the dependency container
     * @param zibo\core\Zibo $zibo Instance of zibo
     * @return zibo\core\dependency\DependencyContainer
     */
    public function getContainer() {
        $container = new DependencyContainer();

        $files = array_reverse($this->fileBrowser->getFiles(Zibo::DIRECTORY_CONFIG . '/' . self::FILE));
        foreach ($files as $file) {
            $this->readDependencies($container, $file);
        }

        if ($this->environment) {
            $files = array_reverse($this->fileBrowser->getFiles(Zibo::DIRECTORY_CONFIG . '/' . $this->environment . '/' . self::FILE));
            foreach ($files as $file) {
                $this->readDependencies($container, $file);
            }
        }

        return $container;
    }

    /**
     * Reads the dependencies from the provided file and adds them to the
     * provided container
     * @param zibo\core\dependency\DependencyContainer $container
     * @param zibo\library\filesystem\File $file
     * @return null
     */
    private function readDependencies(DependencyContainer $container, File $file) {
//          echo $file . "\n";
        $dom = new Document();
        $dom->load($file);

        $dependencyElements = $dom->getElementsByTagName(self::TAG_DEPENDENCY);
        foreach ($dependencyElements as $dependencyElement) {
            $interface = $dependencyElement->getAttribute(self::ATTRIBUTE_INTERFACE);
            $className = $dependencyElement->getAttribute(self::ATTRIBUTE_CLASS);
            $id = $dependencyElement->getAttribute(self::ATTRIBUTE_ID);
            if (!$id) {
                $id = null;
            }

            $extends = $dependencyElement->getAttribute(self::ATTRIBUTE_EXTENDS);
            if ($extends) {
                if (!$interface) {
                    $interface = $className;
                }

                $dependencies = $container->getDependencies($interface);
                if (isset($dependencies[$extends])) {
                    $dependency = clone $dependencies[$extends];
                    $dependency->setId($id);
                    if ($className) {
                        $dependency->setClassName($className);
                    }
                } else {
                    throw new DependencyException('No dependency set to extend interface ' . $interface . ' with id ' . $extends);
                }
            } else {
                $dependency = new Dependency($className, $id);
            }

            $this->readCalls($dependencyElement, $dependency);
            $this->readInterfaces($dependencyElement, $dependency, $interface, $className);

            $container->addDependency($dependency);
        }
    }

    /**
     * Reads the calls from the provided dependency element and adds them to
     * the dependency instance
     * @param DOMElement $dependencyElement
     * @param zibo\core\dependency\Dependency $dependency
     * @return null
     */
    private function readCalls(DOMElement $dependencyElement, Dependency $dependency) {
        $calls = array();

        $callElements = $dependencyElement->getElementsByTagName(self::TAG_CALL);
        foreach ($callElements as $callElement) {
            $methodName = $callElement->getAttribute(self::ATTRIBUTE_METHOD);

            $call = new DependencyCall($methodName);

            $argumentElements = $callElement->getElementsByTagName(self::TAG_ARGUMENT);
            foreach ($argumentElements as $argumentElement) {
                $name = $argumentElement->getAttribute(self::ATTRIBUTE_NAME);
                $type = $argumentElement->getAttribute(self::ATTRIBUTE_TYPE);
                $properties = array();

                $propertyElements = $argumentElement->getElementsByTagName(self::TAG_PROPERTY);
                foreach ($propertyElements as $propertyElement) {
                    $propertyName = $propertyElement->getAttribute(self::ATTRIBUTE_NAME);
                    $propertyValue = $propertyElement->getAttribute(self::ATTRIBUTE_VALUE);

                    $properties[$propertyName] = $propertyValue;
                }

                $call->addArgument(new DependencyCallArgument($name, $type, $properties));
            }

            $dependency->addCall($call);
        }
    }

    /**
     * Reads the interfaces from the provided dependency element and adds them
     * to the dependency instance
     * @param DOMElement $dependencyElement
     * @param zibo\core\dependency\Dependency $dependency
     * @param string $interface Class name of the interface
     * @param string $className Class name of the instance
     * @return null
     */
    private function readInterfaces(DOMElement $dependencyElement, Dependency $dependency, $interface, $className) {
        $interfaces = array();

        $interfaceElements = $dependencyElement->getElementsByTagName(self::ATTRIBUTE_INTERFACE);
        foreach ($interfaceElements as $interfaceElement) {
            $interfaceName = $interfaceElement->getAttribute(self::ATTRIBUTE_NAME);

            $interfaces[$interfaceName] = true;
        }

        if ($interface) {
            $interfaces[$interface] = true;
        }

        if (!$interfaces) {
            $interfaces[$className] = true;
        }

        $dependency->setInterfaces($interfaces);
    }

}