<?php

namespace zibo\library;

use \Exception;
use \RecursiveIterator;
use \RecursiveIteratorIterator;

/**
 * Structured data container (hierarchic array)
 */
class Structure implements RecursiveIterator {

    const ARRAY_OPEN = '[';
    const ARRAY_CLOSE = ']';

    private $array;
    private $id;

    public function __construct(array $array = null, $id = null) {
        if ($array == null) {
            $array = array();
        }
        $this->array = $array;
        $this->id = $id;
    }

    public function getAll() {
        return $this->array;
    }

    public function get($name) {
        $positionOpen = strpos($name, self::ARRAY_OPEN);
        if ($positionOpen === false) {
            return $this->getValue($name);
        }

        $tokens = explode(self::ARRAY_OPEN, $name);

        $value = null;
        $token = array_shift($tokens) . self::ARRAY_CLOSE;
        while ($token != null) {
            $token = $this->checkToken($token, $name);

            $value = $this->getValue($token, $value);
            if ($value === null) {
                return null;
            }

            $token = array_shift($tokens);
        }

        return $value;
    }

    private function getValue($key, array $array = null) {
        if ($array == null) {
            $array = $this->array;
        }

        if (!isset($array[$key])) {
            return null;
        }

        return $array[$key];
    }

    public function set($name, $value) {
        $positionOpen = strpos($name, self::ARRAY_OPEN);
        if ($positionOpen === false) {
            $this->array[$name] = $value;
            return;
        }

        $tokens = explode(self::ARRAY_OPEN, $name);

        $array = &$this->array;
        $previousArray = &$array;

        $token = array_shift($tokens) . self::ARRAY_CLOSE;
        $token = $this->checkToken($token, $name);

        while (!empty($tokens)) {
            if (isset($array[$token]) && is_array($array[$token])) {
                $array = &$array[$token];
            } else {
                $previousArray[$token] = array();
                $array = &$previousArray[$token];
            }

            $previousArray = &$array;
            $token = $this->checkToken(array_shift($tokens), $name);
        }

        $array[$token] = $value;
    }

    public function has($name) {
        $positionOpen = strpos($name, self::ARRAY_OPEN);
        if ($positionOpen === false) {
            return isset($this->array[$name]);
        }

        $tokens = explode(self::ARRAY_OPEN, $name);

        $array = &$this->array;
        $token = array_shift($tokens) . self::ARRAY_CLOSE;

        while ($token != null) {
            $token = $this->checkToken($token, $name);

            if (!isset($array[$token])) {
                return false;
            }

            $array = &$array[$token];
            if (!empty($tokens) && ($array == null || !is_array($array))) {
                return false;
            }

            $token = array_shift($tokens);
        }

        return true;
    }

    public function isEmpty() {
        return empty($this->array);
    }

    private function checkToken($token, $name) {
        $positionClose = strpos($token, self::ARRAY_CLOSE);
        if ($positionClose === false) {
            throw new Exception('Array ' . $token . ' opened but not closed in ' . $name);
        }

        if ($positionClose != (strlen($token) - 1)) {
            throw new Exception('Array not closed before the end of the token in ' . $name);
        }

        return substr($token, 0, -1);
    }

    public function getIterator() {
        return new RecursiveIteratorIterator($this);
    }

    public function rewind() {
        reset($this->array);
    }

    public function current() {
        return current($this->array);
    }

    public function key() {
        if (!empty($this->id)) {
            return $this->id . self::ARRAY_OPEN . key($this->array) . self::ARRAY_CLOSE;
        }

        return key($this->array);
    }

    public function next() {
        return next($this->array);
    }

    public function valid() {
        return $this->current() !== false;
    }

    public function hasChildren() {
        $current = $this->current();
        return is_array($current) && !empty($current);
    }

    public function getChildren() {
        return new self($this->current(), $this->key());
    }

    /**
     * Merges 2 arrays into 1, when the second array has no 0 key, the keys of the second array will be used and thus overwritten in the first if they exist
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function merge(array $array1, array $array2, $nestedMerge = false) {
        $useKey = !array_key_exists(0, $array2);

        foreach ($array2 as $key => $value) {
            if ($useKey) {
                if ($nestedMerge && isset($array1[$key]) && is_array($array1[$key]) && is_array($value)) {
                    $array1[$key] = self::merge($array1[$key], $value, true);
                } else {
                    $array1[$key] = $value;
                }
            } else {
                $array1[] = $value;
            }
        }

        return $array1;
    }

    /**
     * Gets an array with the keys of the provided array as key and as value
     * @param array $array
     * @return array
     */
    public static function getKeyArray(array $array) {
        $keyArray = array();

        foreach ($array as $key => $null) {
            $keyArray[$key] = $key;
        }

        return $keyArray;
    }

}