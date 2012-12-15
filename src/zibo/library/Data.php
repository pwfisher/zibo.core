<?php

namespace zibo\library;

use \Iterator;

/**
 * Generic data container
 */
class Data implements Iterator {

    /**
     * Array for the variables which are not defined in the class
     * @var array
     */
    private $variables;

    /**
     * Sets a inaccessible property
     * @param string $name Name of the property
     * @param mixed $value Value for the property
     * @return null
     */
    public function __set($name, $value) {
        $methodName = $this->getMethodName($name, 'set');

        if (method_exists($this, $methodName)) {
            return $this->$methodName($value);
        }

        if ($this->variables === null) {
            $this->variables = array();
        }

        $this->variables[$name] = $value;
    }

    /**
     * Gets a inaccessible property
     * @param string $name Name of the property
     * @return mixed Value for the property
     */
    public function __get($name) {
        $methodName = $this->getMethodName($name, 'get');

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }
        return null;
    }

    /**
     * Gets whether a inaccessible property is set
     * @param string $name Name of the property
     * @return boolean True when the property is set, false otherwise
     */
    public function __isset($name) {
        if (isset($this->$name)) {
            return true;
        }

        return isset($this->variables[$name]);
    }

    /**
     * Unsets a inaccessible property
     * @param string $name Name of the property
     * @return null
     * @throws zibo\ZiboException when a setter method is available for the property
     */
    public function __unset($name) {
        $methodName = $this->getMethodName($name, 'set');
        if (method_exists($this, $methodName)) {
            throw new Exception('Cannot unset ' . $name . ', use ' . $methodName . ' instead');
        }

        if (isset($this->variables[$name])) {
            unset($this->variables[$name]);
        }
    }

    /**
     * Gets the full method name for the provided property
     * @param string $name Name of the property
     * @param string $prefix Prefix for the method (eg. get or set)
     * @return string Method name
     */
    private function getMethodName($name, $prefix) {
        return $prefix . ucfirst($name);
    }

    /**
     * Resets the pointer of the inaccessible properties
     * @return null
     */
    public function rewind() {
        if ($this->variables) {
            reset($this->variables);
        }
    }

    /**
     * Gets the value of the current property
     * @return mixed
     */
    public function current() {
        if ($this->variables) {
            return current($this->variables);
        }

        return false;
    }

    /**
     * Gets the key of the pointer of the inaccessible properties
     * @return mixed
     */
    public function key() {
        if ($this->variables) {
            return key($this->variables);
        }

        return null;
    }

    /**
     * Sets the pointer of the inaccessible properties to the next property
     * @return mixed The value of the next property
     */
    public function next() {
        if ($this->variables) {
            return next($this->variables);
        }

        return null;
    }

    /**
     * Gets whether the current pointer is a valid one
     * @return boolean True if the pointer is valid, false otherwise
     */
    public function valid() {
        return $this->current() !== false;
    }

}