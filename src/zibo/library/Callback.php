<?php

namespace zibo\library;

use \Exception;
use \ReflectionClass;
use \ReflectionFunction;

/**
 * Callback object for dynamic method invokation
 */
class Callback {

    /**
     * The callback to wrap around
     * @var string|array
     */
    private $callback;

    /**
     * Instance of a class or a class name for a static call
     * @var mixed
     */
    private $class;

    /**
     * The name of a method in the provided class or a function name
     * @var string
     */
    private $method;

    /**
     * A string representation of the callback
     * @var string
     */
    private $callbackString;

    /**
     * Constructs a new callback
     * @param string|array|Callback $callback The callback
     * @return null
     * @throws Exception when the provided callback is invalid
     */
    public function __construct($callback) {
        $this->setCallback($callback);
    }

    /**
     * Gets a string representation of this callback
     * @return string
     */
    public function __toString() {
        if (!$this->class) {
            return $this->method;
        }

        if (is_string($this->class)) {
            return $this->class . '::' . $this->method;
        }

        return get_class($this->class) . '->' . $this->method;
    }

    /**
     * Gets a instance of the class or a class name in case of static call
     * @return mixed
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * Gets the method on the class or if no class is set, a global function
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Sets the callback
     * @param string|array|Callback $callback A string for a function call, an
     * array with as first argument the class name (for static methods) or
     * instance and as a second argument the method name. Another instance of
     * Callback is also possible.
     * @return null
     * @throws Exception when an invalid callback has been provided
     */
    public function setCallback($callback) {
        if ($callback instanceof self) {
            // callback is already an instance of Callback, copy it's variables
            $this->callback = $callback->callback;
            $this->class = $callback->class;
            $this->method = $callback->method;

            return;
        }

        if (is_string($callback) && $callback) {
            // callback is a string: a global function call
            $this->callback = $callback;
            $this->class = null;
            $this->method = $callback;

            return;
        }

        // callback is an array with a class name or class instance as first
        // element and the method as the second element
        if (!is_array($callback)) {
            throw new Exception('Provided callback is invalid: callback is not a string or an array');
        }
        if (count($callback) != 2) {
            throw new Exception('Provided callback is invalid: callback array should have 2 elements');
        }
        if (!isset($callback[0])) {
            throw new Exception('Provided callback is invalid: callback array should have an element 0 containing the class name or a class instance');
        }
        if (!isset($callback[1])) {
            throw new Exception('Provided callback is invalid: callback array should have an element 1 containing the method name');
        }

        $this->class = $callback[0];
        $this->method = $callback[1];

        if (!is_string($this->class) && !is_object($this->class)) {
            throw new Exception('Provided callback is invalid: class parameter is invalid or empty');
        }

        if (!is_string($this->method) || !$this->method) {
            throw new Exception('Provided callback is invalid: method parameter is invalid or empty');
        }

        $this->callback = $callback;
    }

    /**
     * Gets the possible arguments for this callback
     * @return array Array with the name of the argument as key and the type
     * of the argument as value
     */
    public function getArguments() {
        if (!$this->class) {
            $reflectionFunction = new ReflectionFunction($this->method);
            $arguments = $reflectionFunction->getParameters();
        } else {
            $reflectionClass = new ReflectionClass($this->class);
            $reflectionMethod = $reflectionClass->getMethod($this->method);
            $arguments = $reflectionMethod->getParameters();
        }

        return self::parseReflectionArguments($arguments);
    }

    /**
     * Checks if this callback is callable
     * @return boolean True if the callback is callable, false otherwise
     */
    public function isCallable() {
        return is_callable($this->callback);
    }

    /**
     * Invokes the callback. All arguments passed to this method will be passed
     * on to the callback
     * @return mixed The result of the callback
     */
    public function invoke() {
        $arguments = func_get_args();

        return $this->invokeWithArrayArguments($arguments);
    }

    /**
     * Invokes the callback with an array of arguments
     * @param array $arguments The arguments for the callback
     * @return mixed The result of the callback
     */
    public function invokeWithArrayArguments(array $arguments) {
        if (!is_callable($this->callback)) {
            throw new Exception('Could not invoke ' . $this->__toString() . ': callback is not callable');
        }

        return call_user_func_array($this->callback, $arguments);
    }

    /**
     * Parses the arguments
     * @return array Array with the name of the argument as key and the type
     * of the argument as value
     */
    public static function parseReflectionArguments(array $arguments) {
        $argumentNames = array();
        foreach ($arguments as $argument) {
            $type = null;

            $argumentClass = $argument->getClass();
            if ($argumentClass) {
                $type = $argumentClass->getName();
            } elseif ($argument->isArray()) {
                $type = 'array';
            }

            $argumentNames[$argument->getName()] = $type;
        }

        return $argumentNames;
    }

}