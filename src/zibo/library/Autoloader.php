<?php

namespace zibo\library;

use \Exception;

/**
 * Generic autoloader according to PSR. This loader initializes with the PHP
 * include paths.
 *
 * <p>Loads classes with the following types of naming:</p>
 * <ul>
 * <li>zibo\library\Autoloader in <path>/zibo/core/Autloader.php
 * <li>zibo_library_Autoloader in <path>/zibo/library/Autoloader.php
 * <li>zibo_library_Autoloader in <path>/zibo_library_Autoloader.php
 * </ul>
 */
class Autoloader {

    /**
     * The source include paths
     * @var array
     */
    protected $includePaths;

    /**
     * Construct a new autoloader
     * @return null
     */
    public function __construct() {
        $this->includePaths = explode(PATH_SEPARATOR, get_include_path());
    }

    /**
     * Adds a source directory where classes could be found.
     *
     * <p>This directory will be prepended to the current set include paths. This
     * way newly added include paths will be looked through first before
     * checking the general PHP include paths.</p>
     * @param string $path The path of the source directory
     * @return null
     * @throws Exception when the provided path is a invalid value
     */
    public function addIncludePath($path) {
        if (!is_string($path) || !$path) {
            throw new Exception('Provided path is not a string or is empty');
        }

        $path = rtrim($path, '/');

        array_unshift($this->includePaths, $path);
    }

    /**
     * Removes a source directory from the include paths
     * @param string $path The path of a source directory
     * @return null
     */
    public function removeIncludePath($path) {
        foreach ($this->includePaths as $index => $includePath) {
            if ($includePath == $path) {
                unset($this->includePaths[$index]);
            }
        }
    }

    /**
     * Autoloads the provided class
     * @param string $class full class name with namespace
     * @return boolean true if succeeded, false otherwise
     */
    public function autoload($className) {
        $classFile = $className . '.php';
        $namespacedClassFile = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $classFile);

        foreach ($this->includePaths as $includePath) {
            if ($this->autoloadFile($includePath . '/' . $namespacedClassFile)) {
                return true;
            }

            if (strpos($classFile, '\\') === false) {
                if ($this->autoloadFile($includePath . '/' . $classFile)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Tries to include the provided file
     * @param string $fileName The file name of a PHP source
     * @return boolean True if the file is included, false otherwise
     */
    protected function autoloadFile($fileName) {
        $fileName = realpath($fileName);

        if (!$fileName || !is_readable($fileName)) {
            return false;
        }

        include_once($fileName);

        return true;
    }

    /**
     * Registers this autoload implementation to PHP
     * @return null
     * @throws Exception when the autoloader could not be registered
     */
    public function registerAutoloader($prepend = false) {
        if (!spl_autoload_register(array($this, 'autoload'), false, $prepend)) {
            throw new Exception('Could not register this autoloader');
        }
    }

    /**
     * Unegisters this autoload implementation from PHP
     * @return null
     * @throws Exception when the autoloader could not be unregistered
     */
    public function unregisterAutoloader() {
        if (!spl_autoload_unregister(array($this, 'autoload'))) {
            throw new Exception('Could not unregister this autoloader');
        }
    }

}