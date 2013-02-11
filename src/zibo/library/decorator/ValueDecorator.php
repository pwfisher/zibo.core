<?php

namespace zibo\library\decorator;

use zibo\library\Value;

use \Exception;

/**
 * Generic decorator for a scalar value, an object value or an array value
 */
class ValueDecorator implements Decorator {

    /**
     * Field name for objects or arrays passed to this decorator
     * @var string
     */
    protected $fieldName;

    /**
     * Value helper
     * @var zibo\library\Value
     */
    protected $helper;

    /**
     * Constructs a new decorator
     * @param string|array $fieldName Name of the propery, or an array with
     * property names for nested properties
     * @return null
     * @throws Exception when an invalid field name is provided
     */
    public function __construct($fieldName = null) {
        $this->setFieldName($fieldName);
        $this->helper = new Value();
    }

    /**
     * Sets the field name for objects or arrays passed to this decorator
     * @param string|array $fieldName Name of the propery, or an array with
     * property names for nested properties
     * @return null
     * @throws Exception when the field name is empty or an object
     */
    protected function setFieldName($fieldName) {
        if (($fieldName !== null && !is_string($fieldName) && !is_integer($fieldName) && !is_array($fieldName)) || $fieldName == '') {
            throw new Exception('Provided field name is empty or invalid');
        }

        $this->fieldName = $fieldName;
    }

    /**
     * Gets the value to decorate, passes it through the decorateValue method
     * @param mixed $value Value to decorate
     * @return mixed Decorated value
     */
    public function decorate($value) {
        $value = $this->getValue($value);
        $value = $this->decorateValue($value);

        return $value;
    }

    /**
     * Performs the actual decorating on the provided value.
     * @param mixed $value The value to decorate
     * @return mixed The decorated value.
     * the class name for objects and 'Array' for arrays.
     */
    protected function decorateValue($value) {
        return $this->helper->toString($value);
    }

    /**
     * Gets the value from the provided cell, depending on the value type and
     * the field name set to this decorator.
     * @param mixed $value The initial decorate value
     * @return mixed Value of the set field in the initial decorate value
     */
    protected function getValue($value) {
        return $this->helper->getProperty($value, $this->fieldName, $value);
    }

}