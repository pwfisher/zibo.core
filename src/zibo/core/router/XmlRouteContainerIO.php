<?php

namespace zibo\core\router;

use zibo\library\dependency\DependencyCallArgument;

use zibo\core\environment\dependency\io\ZiboXmlDependencyIO;
use zibo\core\environment\Environment;
use zibo\core\Zibo;

use zibo\library\filesystem\File;
use zibo\library\router\exception\RouterException;
use zibo\library\router\Route;
use zibo\library\router\RouteContainer;
use zibo\library\xml\dom\Document;
use zibo\library\Boolean;

use \DOMElement;
use \Exception;

/**
 * XML implementation of the RouterIO
 */
class XmlRouteContainerIO implements RouteContainerIO {

    /**
     * Configuration key for the routes schema file
     * @var string
     */
    const PARAM_RNG = 'schema.routes';

    /**
     * Path to the xml file for the routes
     * @var string
     */
    const PATH_FILE = 'config/routes.xml';

    /**
     * Name of the root tag
     * @var string
     */
    const TAG_ROOT = 'routes';

    /**
    * Name of the route tag
    * @var string
    */
    const TAG_ROUTE = 'route';

    /**
     * Name of the base URL attribute
     * @var string
     */
    const ATTRIBUTE_BASE = 'base';

    /**
     * Name of the path attribute
     * @var string
     */
    const ATTRIBUTE_PATH = 'path';

    /**
     * Name of the controller attribute
     * @var string
     */
    const ATTRIBUTE_CONTROLLER = 'controller';

    /**
     * Name of the action attribute
     * @var string
     */
    const ATTRIBUTE_ACTION = 'action';

    /**
     * Name of the id attribute
     * @var string
     */
    const ATTRIBUTE_ID = 'id';

    /**
     * Name of the allowed methods attribute
     * @var string
     */
    const ATTRIBUTE_ALLOWED_METHODS = 'methods';

    /**
     * Name of the dynamic attribute
     * @var string
     */
    const ATTRIBUTE_DYNAMIC = 'dynamic';

    /**
     * Name of the locale attribute
     * @var string
     */
    const ATTRIBUTE_LOCALE = 'locale';

    /**
     * Default action
     * @var string
     */
    const DEFAULT_ACTION = 'indexAction';

    /**
     * Instance of the environment
     * @var zibo\core\environment\Environment
     */
    protected $environment;

    /**
     * The loaded route container
     * @var zibo\library\router\RouteContainer
     */
    protected $routeContainer;

    /**
     * Constructs a new XML router IO implementation
     * @param zibo\core\environment\Environment $environment Instance of the
     * environment
     * @return null
     */
    public function __construct(Environment $environment) {
        $this->environment = $environment;
    }

    /**
     * Gets the route container
     * @return zibo\library\router\RouteContainer
     */
    public function getRouteContainer() {
        if (!$this->routeContainer) {
            $this->readContainer();
        }

        return $this->routeContainer;
    }

    /**
     * Reads the containers from the data source
     * @return null
     */
    protected function readContainer() {
        $this->routeContainer = new RouteContainer();

    	$files = array_reverse($this->environment->getFileBrowser()->getFiles(self::PATH_FILE));
    	foreach ($files as $file) {
    		$this->readContainerFromFile($this->routeContainer, $file);
    	}
    }

    /**
     * Reads the aliases from the provided file
     * @param zibo\library\filesystem\File $file
     * @return null
     */
    private function readContainerFromFile(RouteContainer $routeContainer, File $file) {
    	$dom = new Document();

        $relaxNg = $this->environment->getConfig()->get(self::PARAM_RNG);
        if ($relaxNg) {
        	$dom->setRelaxNGFile($relaxNg);
        }

    	$dom->load($file);

    	$this->readRoutesFromElement($routeContainer, $file, $dom->documentElement);
    }

    /**
     * Gets the routes object from an XML routes element
     * @param zibo\library\filesystem\File $file the file which is being
     * read
     * @param DomElement $routesElement the element which contains route
     * elements
     * @return null
     */
    private function readRoutesFromElement(RouteContainer $routeContainer, File $file, DOMElement $routesElement) {
        $elements = $routesElement->getElementsByTagName(self::TAG_ROUTE);
        foreach ($elements as $element) {
        	$path = $this->getAttribute($file, $element, self::ATTRIBUTE_PATH);

        	$controller = $this->getAttribute($file, $element, self::ATTRIBUTE_CONTROLLER);
        	$action = $this->getAttribute($file, $element, self::ATTRIBUTE_ACTION, false);
        	if (!$action) {
        	    $action = self::DEFAULT_ACTION;
        	}
        	$callback = array($controller, $action);

        	$id = $this->getAttribute($file, $element, self::ATTRIBUTE_ID, false);
        	if (!$id) {
        	    $id = null;
        	}

        	$allowedMethods = $this->getAttribute($file, $element, self::ATTRIBUTE_ALLOWED_METHODS, false);
        	if ($allowedMethods) {
        	    $allowedMethods = explode(',', $allowedMethods);
        	} else {
        	    $allowedMethods = null;
        	}

        	$route = new Route($path, $callback, $id, $allowedMethods);

        	$isDynamic = $this->getAttribute($file, $element, self::ATTRIBUTE_DYNAMIC, false);
        	if ($isDynamic !== '') {
        	    $route->setIsDynamic(Boolean::getBoolean($isDynamic));
        	}

        	$arguments = $this->readArgumentsFromRouteElement($file, $element);
        	if ($arguments) {
        	    $route->setPredefinedArguments($arguments);
        	}

        	$locale = $this->getAttribute($file, $element, self::ATTRIBUTE_LOCALE, false);
        	if ($locale !== '') {
        	    $route->setLocale($locale);
        	}

        	$baseUrl = $this->getAttribute($file, $element, self::ATTRIBUTE_BASE, false);
        	if ($baseUrl) {
        	    $route->setBaseUrl($baseUrl);
        	}

        	$routeContainer->addRoute($route);
        }
    }

    /**
     * Gets the routes object from an XML routes element
     * @param zibo\library\filesystem\File $file the file which is being
     * read
     * @param DomElement $routesElement the element which contains route
     * elements
     * @return null
     */
    private function readArgumentsFromRouteElement(File $file, DOMElement $routeElement) {
        $arguments = array();

        $argumentElements = $routeElement->getElementsByTagName(ZiboXmlDependencyIO::TAG_ARGUMENT);
        foreach ($argumentElements as $argumentElement) {
            $name = $argumentElement->getAttribute(ZiboXmlDependencyIO::ATTRIBUTE_NAME);
            $type = $argumentElement->getAttribute(ZiboXmlDependencyIO::ATTRIBUTE_TYPE);
            $properties = array();

            $propertyElements = $argumentElement->getElementsByTagName(ZiboXmlDependencyIO::TAG_PROPERTY);
            foreach ($propertyElements as $propertyElement) {
                $propertyName = $propertyElement->getAttribute(ZiboXmlDependencyIO::ATTRIBUTE_NAME);
                $propertyValue = $propertyElement->getAttribute(ZiboXmlDependencyIO::ATTRIBUTE_VALUE);

                $properties[$propertyName] = $propertyValue;
            }

            $arguments[$name] = new DependencyCallArgument($name, $type, $properties);
        }

        return $arguments;
    }

    /**
     * Gets the value of an attribute from the provided XML element
     * @param zibo\library\filesystem\File $file the file which is being read
     * @param DomElement $element the element from which the attribute needs to
     * be retrieved
     * @param string $name name of the attribute
     * @param boolean $required flag to see if the value is required or not
     * @return string
     * @throws zibo\library\router\exception\RouterException when the attribute
     * is required but not set or empty
     */
    private function getAttribute(File $file, DOMElement $element, $name, $required = true) {
        $value = $element->getAttribute($name);

        if ($required && empty($value)) {
            throw new RouterException('Attribute ' . $name . ' not set in ' . $file->getPath());
        }

        return $value;
    }

    /**
     * Sets the route container to the data source
     * @param zibo\library\router\RouteContainer $container The container to write
     * @return null
     */
    public function setRouteContainer(RouteContainer $container) {
        $fileBrowser = $this->environment->getFileBrowser();

        $xmlFile = new File($fileBrowser->getApplicationDirectory() . '/' . self::PATH_FILE);

        $routes = $container->getRoutes();

        // read the current routes not defined in application
        $xmlRouteContainer = new RouteContainer();

        $files = array_reverse($fileBrowser->getFiles(self::PATH_FILE));
        foreach ($files as $file) {
            if (strpos($file->getPath(), $xmlFile->getPath()) !== false) {
                continue;
            }

            $this->readContainersFromFile($xmlRouteContainer, $file);
        }

        // filter the routes which are not defined in application
        $xmlRoutes = $xmlRouteContainer->getRoutes();
        foreach ($routes as $path => $route) {
            foreach ($xmlRoutes as $xmlRoute) {
                if ($xmlRoute->equals($route)) {
                    unset($routes[$path]);
                }
            }
        }

        if (!$routes) {
            // no routes left to write
            if ($xmlFile->exists()) {
                $xmlFile->delete();
            }

            return;
        }

        // write the routes
        $dom = new Document('1.0', 'utf-8');
        $dom->formatOutput = true;

        $routesElement = $dom->createElement(self::TAG_ROOT);
        $dom->appendChild($routesElement);

        foreach ($routes as $route) {
            $callback = $route->getCallback();
            $id = $route->getId();
            $allowedMethods = $route->getAllowedMethods();
            $predefinedArguments = $route->getPredefinedArguments();
            $locale = $route->getLocale();
            $baseUrl = $route->getBaseUrl();

            $routeElement = $dom->createElement(self::TAG_ROUTE);
            $routeElement->setAttribute(self::ATTRIBUTE_PATH, $route->getPath());
            $routeElement->setAttribute(self::ATTRIBUTE_CONTROLLER, $callback->getClass());
            $routeElement->setAttribute(self::ATTRIBUTE_ACTION, $callback->getMethod());

            if ($id !== null) {
                $routeElement->setAttribute(self::ATTRIBUTE_ID, $id);
            }

            if ($allowedMethods) {
                $routeElement->setAttribute(self::ATTRIBUTE_ALLOWED_METHODS, implode(',', $allowedMethods));
            }

            if ($route->isDynamic()) {
                $routeElement->setAttribute(self::ATTRIBUTE_DYNAMIC, 'true');
            }

            if ($predefinedArguments) {
                foreach ($predefinedArguments as $argument) {
                    if (!$argument instanceof DependencyCallArgument) {
                        throw new RouterException('Invalid predefined argument for route ' . $route->getPath());
                    }

                    $argumentElement = $dom->createElement(ZiboXmlDependencyIO::TAG_ARGUMENT);
                    $argumentElement->setAttribute(ZiboXmlDependencyIO::ATTRIBUTE_NAME, $argument->getName());
                    $argumentElement->setAttribute(ZiboXmlDependencyIO::ATTRIBUTE_TYPE, $argument->getType());

                    $properties = $argument->getProperties();
                    foreach ($properties as $key => $value) {
                        $propertyElement = $dom->createElement(ZiboXmlDependencyIO::TAG_PROPERTY);
                        $propertyElement->setAttribute(ZiboXmlDependencyIO::ATTRIBUTE_NAME, $key);
                        $propertyElement->setAttribute(ZiboXmlDependencyIO::ATTRIBUTE_VALUE, $value);

                        $argumentElement->appendChild($propertyElement);
                    }

                    $routeElement->appendChild($argumentElement);
                }
            }

            if ($locale) {
                $routeElement->setAttribute(self::ATTRIBUTE_LOCALE, $locale);
            }

            if ($baseUrl) {
                $routeElement->setAttribute(self::ATTRIBUTE_BASE, $baseUrl);
            }

            $importedRouteElement = $dom->importNode($routeElement, true);
            $routesElement->appendChild($importedRouteElement);
        }

        $dom->save($xmlFile);
    }

}