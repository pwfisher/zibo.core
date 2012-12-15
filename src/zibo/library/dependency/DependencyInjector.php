<?php

namespace zibo\library\dependency;

use zibo\library\dependency\argument\ArgumentParser;
use zibo\library\dependency\argument\ArrayArgumentParser;
use zibo\library\dependency\argument\CallArgumentParser;
use zibo\library\dependency\argument\DependencyArgumentParser;
use zibo\library\dependency\argument\InjectableArgumentParser;
use zibo\library\dependency\argument\NullArgumentParser;
use zibo\library\dependency\argument\ScalarArgumentParser;
use zibo\library\dependency\exception\DependencyException;
use zibo\library\dependency\exception\DependencyNotFoundException;

use zibo\library\Callback;
use zibo\library\ObjectFactory;

use \Exception;

/**
 * Implementation of a dependency injector. Load class instances dynamically
 * from a dependency container when and only when needed.
 */
class DependencyInjector {

	/**
	 * Call argument type
	 * @var string
	 */
	const TYPE_CALL = 'call';

	/**
	 * Dependency argument type
	 * @var string
	 */
	const TYPE_DEPENDENCY = 'dependency';

	/**
	 * Null value argument type
	 * @var string
	 */
	const TYPE_NULL = 'null';

	/**
	 * Scalar value argument type
	 * @var string
	 */
	const TYPE_SCALAR = 'scalar';

	/**
	 * ARray value argument type
	 * @var string
	 */
	const TYPE_ARRAY = 'array';

    /**
     * Instance of the object factory
     * @var zibo\library\ObjectFactory
     */
    protected static $objectFactory;

    /**
     * Array with the argument parsers
     * @var array
     */
    protected static $argumentParsers;

    /**
     * Container of the injection dependencies
     * @var DependencyContainer
     */
    protected static $container;

    /**
     * Created dependency instances
     * @var array
     */
    protected static $instances;

    /**
     * Initializes the core argument parsers
     * @return null
     */
    protected function initArgumentParsers() {
    	self::$argumentParsers = array(
    		self::TYPE_NULL => new NullArgumentParser(),
    		self::TYPE_SCALAR => new ScalarArgumentParser(),
    		self::TYPE_ARRAY => new ArrayArgumentParser(),
    		self::TYPE_DEPENDENCY => new DependencyArgumentParser(),
    		self::TYPE_CALL => new CallArgumentParser(),
    	);
    }

    /**
     * Sets a argument parser for the provided type
     * @param string $type The name of the argument type
     * @param ArgumentParser $argumentParser The parser for this type
     * @return null
     * @throws Exception when the provided type is empty or not a string
     */
    public function setArgumentParser($type, ArgumentParser $argumentParser = null) {
    	if (!is_string($type) || !$type) {
    		throw new DependencyException('Provided type is empty or not a string');
    	}

    	if (!isset(self::$argumentParsers)) {
    		$this->initArgumentParsers();
    	}

    	if ($argumentParser) {
    		self::$argumentParsers[$type] = $argumentParser;
    	} elseif (isset(self::$argumentParsers[$type])) {
    		unset(self::$argumentParsers[$type]);
    	}
    }

    /**
     * Gets the argument parsers
     * @return array Array with the type as key and the argument parser as value
     */
    public function getArgumentParsers() {
    	if (!isset(self::$argumentParsers)) {
    		$this->initArgumentParsers();
    	}

    	return self::$argumentParsers;
    }

    /**
     * Sets the container of the dependencies. All created instances will be reset.
     * @param zibo\core\dependency\DependencyContainer $container The container to set
     * @param boolean $clearInstances Set to true to clear all loaded instances
     * @return null
     */
    public function setContainer(DependencyContainer $container, $clearInstances = false) {
        self::$container = $container;

        if ($clearInstances) {
            self::$instances = null;
        }
    }

    /**
     * Gets the container of the dependencies
     * @return zibo\core\dependency\InjectionDefinitionContainer
     */
    public function getContainer() {
        if (self::$container) {
            return self::$container;
        }

        self::$container = new DependencyContainer();

        return self::$container;
    }

    /**
     * Overrides the container by setting an instance which will always be
     * returned by get if the provided object's class name is requested
     * @param object $instance Instance to set
     * @param string $interface Interface to set the instance for, if not provided
     * the class name of the instance will be used as interface
     * @return null
     * @throws Exception if the provided instance is not a object
     * @throws Exception if the provided interface is empty or invalid
     */
    public function setInstance($instance, $interface = null) {
        if (!is_object($instance)) {
            throw new DependencyException('Provided instance is not an object');
        }

        if ($interface !== null) {
            if (!is_string($interface) || !$interface) {
                throw new DependencyException('Provided interface is empty or invalid');
            }
        } else {
            $interface = get_class($instance);
        }

        if (!isset(self::$instances)) {
            self::$instances = array($interface => $instance);
        } else {
            self::$instances[$interface] = $instance;
        }
    }

    /**
     * Gets all the defined instances of the provided class
     * @param string $interface The full class name of the interface or parent
     * class
     * @return array
     */
    public function getAll($interface) {
        $interfaceDependencies = array();

        $container = $this->getContainer();
        $dependencies = $container->getDependencies($interface);
        foreach ($dependencies as $dependency) {
            $id = $dependency->getId();
            $interfaceDependencies[$id] = $this->get($interface, $id);
        }

        return $interfaceDependencies;
    }

    /**
     * Gets a defined instance of the provided class
     * @param string $interface The full class name of the interface or parent
     * class
     * @param string $id The id of the dependency to get a specific definition.
     * If an id is provided,the exclude array will be ignored
     * @param array $arguments Array with the arguments for the constructor of
     * the interface. Passing arguments will always result in a new instance.
     * @param array $exclude Array with the interface as key and an array with
     * id's of dependencies as key to exclude from this get call. You should not
     * set this argument, this is used in recursive calls for the actual
     * dependency injection.
     * @return mixed Instance of the requested class
     * @throws zibo\library\dependency\exceptin\DependencyException if the class name
     * or the id are invalid
     * @throws zibo\library\dependency\exception\DependencyException if the dependency
     * could not be created
     */
    public function get($interface, $id = null, array $arguments = null, array $exclude = null) {
        if (!is_string($interface) || !$interface) {
            throw new DependencyException('Provided class name is empty or invalid');
        }

        if (isset(self::$instances[$interface]) && !is_array(self::$instances[$interface]) && $arguments === null) {
            // an instance of this interface is manually set, return it
            return self::$instances[$interface];
        }

        $container = $this->getContainer();
        $dependencies = $container->getDependencies($interface);

        $dependency = null;

        if ($id !== null) {
            // gets a specific instance of the provided interface
            if (!is_string($id) || !$id) {
                throw new DependencyException('Provided id of the injection dependency is empty or invalid');
            }

            if (isset(self::$instances[$interface][$id]) && $arguments === null) {
                // the instance is already created
                return self::$instances[$interface][$id];
            }

            if (!isset($dependencies[$id])) {
                throw new DependencyNotFoundException('No injectable dependency set for ' . $interface . ' with id ' . $id);
            }

            $dependency = $dependencies[$id];
        } else {
            if ($arguments === null && isset(self::$instances[$interface])) {
                // already a instance of the interface set
                $instances = array_reverse(self::$instances[$interface]);

                // gets the last created dependency which is not excluded
                do {
                    $instance = each($instances);
                    if (!$instance) {
                        break;
                    }

                    $id = $instance[0];
                    $instance = $instance[1];
                } while (isset($exclude[$interface][$id]));

                if ($instance) {
                    // there is a dependency created which is not excluded
                    return $instance;
                }
            }

            // no instances created or all are excluded, try to create a new one
            if (!$dependencies) {
                throw new DependencyNotFoundException('No injectable dependencies set for ' . $interface);
            }

            // gets the last defined dependency which is not excluded
            do {
                $dependency = array_pop($dependencies);
                if (!$dependency) {
                    throw new DependencyNotFoundException('No injectable dependency available for ' . $interface);
                }

                $id = $dependency->getId();
            } while (isset($exclude[$interface][$id]));
        }

        // creates a new instance
        try {
            $instance = $this->create($interface, $dependency, $arguments, $exclude);
        } catch (Exception $exception) {
            throw new DependencyException('Could not create the instance of interface ' . $interface . ' with id ' . $id, 0, $exception);
        }

        if ($arguments !== null) {
            // arguments provided, act as factory and don't store the instance
            return $instance;
        }

        // index this interface
        if (!isset(self::$instances[$interface])) {
            self::$instances[$interface] = array();
        } elseif (!isset(self::$instances)) {
            self::$instances = array($interface => array());
        }

        self::$instances[$interface][$id] = $instance;

        return $instance;
    }

    /**
     * Creates an instance of the provided dependency
     * @param string $interface Full class name of the interface or parent class
     * @param Dependency $dependency Definition of the class to create
     * @param array $arguments Arguments for the constructor of the instance
     * @param array $exclude Array with the interface as key and an array with
     * id's of dependencies as key to exclude from the get calls.
     * @return mixed Instance of the dependency
     * @throws Exception when the dependency could not be created
     */
    protected function create($interface, Dependency $dependency, array $arguments = null, array $exclude = null) {
        if (!self::$objectFactory) {
            self::$objectFactory = new ObjectFactory();
        }

        if (!$exclude) {
            $exclude = array($interface => array($dependency->getId() => true));
        } elseif (!isset($exclude[$interface])) {
            $exclude[$interface] = array($dependency->getId() => true);
        } else {
            $exclude[$interface][$dependency->getId()] = true;
        }

        if (!isset(self::$argumentParsers)) {
            $this->initArgumentParsers();
        }

        foreach (self::$argumentParsers as $argumentParser) {
            if ($argumentParser instanceof InjectableArgumentParser) {
                $argumentParser->setDependencyInjector($this);
                $argumentParser->setExclude($exclude);
            }
        }

        $className = $dependency->getClassName();
        $constructorArguments = $dependency->getConstructorArguments();

        $instanceArguments = self::$objectFactory->getArguments($className);

        $constructorArguments = $this->getCallbackArguments($constructorArguments, $exclude);
        $constructorArguments = $this->parseArguments($constructorArguments, $instanceArguments);
        if ($arguments !== null) {
            $arguments = $this->parseArguments($arguments, $constructorArguments);
            $invokeCalls = false;
        } else {
            $arguments = $constructorArguments;
            $invokeCalls = true;
        }

        $instance = self::$objectFactory->create($className, $interface, !$arguments ? null : $arguments);

        if (!$invokeCalls) {
            return $instance;
        }

        $calls = $dependency->getCalls();
        if ($calls) {
            foreach ($calls as $call) {
                $callback = new Callback(array($instance, $call->getMethodName()));
                $callbackArguments = $callback->getArguments();

                $arguments = $this->getCallbackArguments($call->getArguments());
                $arguments = $this->parseArguments($arguments, $callbackArguments);

                $callback->invokeWithArrayArguments($arguments);
            }
        }

        return $instance;
    }

    /**
     * Parses the provided arguments into the argument definition
     * @param array $arguments Provided arguments
     * @param array $definedArguments Argument definition
     * @return array Argument array ready for invokation
     */
    protected function parseArguments(array $arguments, array $definedArguments) {
        foreach ($definedArguments as $argumentName => $argumentType) {
            if (isset($arguments[$argumentName]) || array_key_exists($argumentName, $arguments) !== false) {
                $definedArguments[$argumentName] = $arguments[$argumentName];
                unset($arguments[$argumentName]);
            } elseif ($argumentType == 'array') {
                $definedArguments[$argumentName] = array();
            } elseif ($argumentType) {
                $definedArguments[$argumentName] = null;
            }
        }

        if ($arguments) {
            // more arguments provided then defined, throw exception
            $argumentNames = array();
            $argumentCount = 0;
            foreach ($arguments as $name => $value) {
                $argumentNames[] = $name;
                $argumentCount++;
            }

            $message = implode(', ', $argumentNames);
            if ($argumentCount == 1) {
                $message .= ' is';
            } else {
                $message .= ' are';
            }

            throw new DependencyException($message . ' not defined in the method signature');
        }

        return $definedArguments;
    }

    /**
     * Gets the actual values of the provided arguments
     * @param array $arguments Array of dependency call arguments
     * @return array Array with the values of the call arguments
     * @see DependencyCallArgument
     */
    protected function getCallbackArguments(array $arguments = null) {
        $callArguments = array();

        if ($arguments === null) {
            return $callArguments;
        }

        foreach ($arguments as $name => $argument) {
        	$type = $argument->getType();
        	if (!isset(self::$argumentParsers[$type])) {
        		throw new DependencyException('No argument parser set for type ' . $type);
        	}

        	$callArguments[$name] = self::$argumentParsers[$type]->getValue($argument);
        }

        return $callArguments;
    }

}