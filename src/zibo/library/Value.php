<?php

namespace zibo\library;

/**
 * Helper methods to work with generic values
 */
class Value {

    /**
     * Gets a string representation of the provided value
     * @param mixed $value The value
     * @return string The value for scalar values. For class instances the
     * string representation if possible, the class name otherwise. Array
     * values will be formatted recursively and separated by ,
     */
    public function toString($value) {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $value->__toString();
            } else {
                return get_class($value);
            }
        }

        if (is_array($value)) {
            $items = array();

            foreach ($value as $item) {
                $items[] = $this->toString($item);
            }

            return implode(', ', $items);
        }

        if (is_resource($value)) {
            return '#resource';
        }

        return null;
    }

    /**
     * Gets a property of a value, depending on the value type and the field
     * name
     * @param mixed $value The original value to retrieve the property from
     * @param string|array $property Name of the property or an array with
     * recursive properties
     *
     * <p>eg. Value is a object value:</p>
     * <pre>{
     *     'name': 'Doe',
     *     'firstname': 'John',
     *     'parameters': array(
     *         'param1': 'value1',
     *         'param2': 'value2',
     *     )
     * }</pre>
     *
     * <p>If the provided $property is 'name', the result will be 'Doe',</p>
     * <p>If the provided $property is array('parameters', 'param2'), the
     * result will be 'value2'</p>
     * @param mixed $default The default value
     * @return mixed Value of the property in the original value
     */
    public function getProperty($value, $property, $default = null) {
        if (!is_array($property)) {
            return $this->getPropertyValue($value, $property, $default);
        }

        foreach ($property as $propertyToken) {
            $value = $this->getPropertyValue($value, $propertyToken);

            if ($value === null) {
                $value = $default;
                break;
            }
        }

        return $value;
    }

    /**
     * Gets the property with the provided name of the provided value
     * @param mixed $value The original value to retrieve the property from
     * @param string $property The name of the property
     * @param mixed $default The default value
     * @return mixed The property of the value if found, the provided default
     * value otherwise
     */
    protected function getPropertyValue($value, $property, $default = null) {
        if (is_array($value)) {
            if (isset($value[$property])) {
                return $value[$property];
            }

            return $default;
        }

        if (is_object($value)) {
            if (isset($value->$fieldName)) {
                return $value->$fieldName;
            }
        }

        return $default;
    }

}