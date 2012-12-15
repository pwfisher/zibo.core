<?php

namespace zibo\core\environment;

use zibo\core\environment\filebrowser\FileBrowser;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

/**
 * Autoloader for the Zibo system according the Zibo directory structure
 */
class Autoloader {

    /**
     * A file browser to lookup files
     * @var zibo\core\environment\filebrowser\FileBrowser
     */
    protected $fileBrowser;

    /**
     * Construct a new autoloader
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser A file
     * browser to lookup files
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser) {
        $this->fileBrowser = $fileBrowser;

        // make sure zibo is loaded
        Zibo::VERSION;
    }

    /**
     * Gets the file browser used by this autoloader
     * @return zibo\core\filesystem\FileBrowser
     */
    public function getFileBrowser() {
        return $this->fileBrowser;
    }

    /**
     * Autoloads the provided class
     * @param string $className The full class name with namespace
     * @return boolean True if succeeded, false otherwise
     */
    public function autoload($className) {
        $classFile = $className . '.php';
        $namespacedClassFile = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $classFile);

        $file = $this->fileBrowser->getFile(Zibo::DIRECTORY_SOURCE . File::DIRECTORY_SEPARATOR . $namespacedClassFile);
        if ($file) {
            include_once($file->getPath());
            return true;
        }

        $file = $this->fileBrowser->getFile(Zibo::DIRECTORY_VENDOR . File::DIRECTORY_SEPARATOR . $namespacedClassFile);
        if ($file) {
            include_once($file->getPath());
            return true;
        }

        $file = $this->fileBrowser->getFile(Zibo::DIRECTORY_VENDOR . File::DIRECTORY_SEPARATOR . $classFile);
        if ($file) {
            include_once($file->getPath());
            return true;
        }

        return false;
    }

    /**
     * Registers this autoload implementation to PHP
     * @param boolean $prepend Set to true to prepend to the autoload stack
     * @return null
     */
    public function registerAutoloader($prepend = false) {
        if (!spl_autoload_register(array($this, 'autoload'), false, $prepend)) {
            throw new ZiboException('Could not register this autoloader');
        }
    }

    /**
     * Unegisters this autoload implementation from PHP
     * @return null
     */
    public function unregisterAutoloader() {
        if (!spl_autoload_unregister(array($this, 'autoload'))) {
            throw new ZiboException('Could not unregister this autoloader');
        }
    }

}