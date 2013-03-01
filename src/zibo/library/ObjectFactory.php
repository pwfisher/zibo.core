<?php

namespace zibo\library;

use \ReflectionClass;
use \ReflectionException;
use \Exception;

/**
 * Create objects on the fly by their class name and optional class interface
 * (implements or extends)
 */
class ObjectFactory {

    /**
     * Initializes an instance of the provided class
     * @param string $class Full name of the class
     * @param string|null $neededClass Full name of the interface or parent class
     * @param array|null $arguments Named arguments for the constructor
     * @return mixed New instance of the requested class
     * @throws Exception when an invalid argument is provided
     * @throws Exception when the class does not exists
     * @throws Exception when the class does not implement/extend the provided
     * needed class
     */
    public function create($class, $neededClass = null, array $arguments = null) {
    	if (!is_string($class) || !$class) {
			throw new Exception('Provided class is empty or not a string');
    	}

        try {
            $classReflection = new ReflectionClass($class);
        } catch (Exception $e) {
            throw new Exception('Class ' . $class . ' not found', 0, $e);
        }

        if ($neededClass && $class != $neededClass) {
	    	if (!is_string($neededClass)) {
				throw new Exception('Provided needed class is empty or not a string');
	    	}

            try {
                $neededClassReflection = new ReflectionClass($neededClass);
            } catch (Exception $e) {
                throw new Exception('Needed class ' . $neededClass . ' not found', 0, $e);
            }

            if ($neededClassReflection->isInterface() && !$classReflection->implementsInterface($neededClass)) {
                throw new Exception($class . ' does not implement ' . $neededClass);
            } elseif (!$classReflection->isSubclassOf($neededClass)) {
                throw new Exception($class . ' does not extend ' . $neededClass);
            }
        }

        if (is_null($arguments) || !$arguments) {
            $instance = $classReflection->newInstance();
        } else {
        	$instance = $classReflection->newInstanceArgs($arguments);
        }

        return $instance;
    }

    /**
     * Creates a data instance
     * @param string $class Full name of the data class
     * @param array $values Values for the data
     * @return mixed Instance of the data object
     */
    public function createData($class, array $values) {
        $arguments = $this->getArguments($class);
        foreach ($arguments as $name => $type) {
            if (isset($values[$name])) {
                $arguments[$name] = $values[$name];
                unset($values[$name]);
            } else {
                $arguments[$name] = null;
            }
        }

        $data = $this->create($class, null, $arguments);

        foreach ($values as $name => $value) {
            $this->setProperty($name, $data, $value);
        }

        return $data;
    }

    /**
     * Gets the possible arguments for the constructor of the provided class
     * @param string $class Full name of the class
     * @return array Array with the name of the argument as key and the type
     * of the argument as value
     */
    public function getArguments($class) {
        $reflectionClass = new ReflectionClass($class);
        try {
            $reflectionMethod = $reflectionClass->getMethod('__construct');
            $arguments = $reflectionMethod->getParameters();
        } catch (Exception $e) {
            $arguments = array();
        }

        return Callback::parseReflectionArguments($arguments);
    }


    /**
     * Gets a property of the provided data
     * @param string $name Name of the property
     * @param array|object $data Data container
     * @return mixed Value of the property if found, null otherwise
     */
    public function getProperty($name, &$data) {
        if (is_array($data)) {
            if (isset($data[$name])) {
                return $data[$name];
            }

            return null;
        }

        $methodName = 'get' . ucfirst($name);
        if (method_exists($data, $methodName)) {
            return $data->$methodName();
        }

        if (isset($data->$name)) {
            return $data->$name;
        }

        return null;
    }

    /**
     * Sets a property to the provided data
     * @param string $name Name of the property
     * @param array|object $data Data container
     * @param mixed $value Value for the property
     * @return null
     */
    public function setProperty($name, &$data, $value) {
        if (is_array($data)) {
            $data[$name] = $value;

            return;
        }

        $methodName = 'set' . ucfirst($name);
        if (method_exists($data, $methodName)) {
            $data->$methodName($value);
        } else {
            $data->$name= $value;
        }
    }

}