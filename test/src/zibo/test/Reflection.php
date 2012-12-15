<?php

namespace zibo\test;

use \ReflectionClass;
use \ReflectionProperty;

final class Reflection {

    private function __construct() {
    }

    public static function setProperty($instance, $property, $value) {
        $reflectionProperty = new ReflectionProperty(get_class($instance), $property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, $value);
    }

    public static function getProperty($instance, $property) {
        $reflectionProperty = new ReflectionProperty(get_class($instance), $property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($instance);
    }

    public static function invokeMethod($instance, $method, $args = null) {
        $reflectionClass = new ReflectionClass(get_class($instance));
        $reflectionMethod = $reflectionClass->getMethod($method);

        if ($args == null) {
            $args = array();
        }
        if (!is_array($args)) {
            $args = array($args);
        }

        return $reflectionMethod->invokeArgs($instance, $args);
    }

}