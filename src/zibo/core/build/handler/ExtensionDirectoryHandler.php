<?php

namespace zibo\core\build\handler;

use zibo\library\filesystem\File;

/**
 * Default copy implementation of a DirectoryHandler
 */
class ExtensionDirectoryHandler implements DirectoryHandler {

    /**
     * The default file handler
     * @var FileHandler
     */
    private $defaultFileHandler;

    /**
     * Array with a file handler per extension
     * @var array
     */
    private $extensionHandlers;

    /**
     * Constructs a new extension directory handler
     * @param FileHandler $defaultFileHandler The default file handler
     * @return null
     */
    public function __construct(FileHandler $defaultFileHandler) {
        $this->defaultFileHandler = $defaultFileHandler;
        $this->extensionHandlers = array();
    }

    /**
     * Sets a file handler for a extension
     * @param string $extension Extension to handle
     * @param FileHandler $fileHandler Implementation for the handler
     * @return null
     */
    public function setFileHandler($extension, FileHandler $fileHandler) {
        $this->extensionFileHandlers[$extension] = $fileHandler;
    }

    /**
     * Handles a directory in the a Zibo module
     * @param zibo\library\filesystem\File $source The source directory
     * @param zibo\library\filesystem\File $destination The destination
     * directory
     * @param array $exclude Excluded file names as key of the array
     * @return null
     */
    public function handleDirectory(File $source, File $destination, array $exclude) {
        $files = $source->read();
        foreach ($files as $file) {
            $name = $file->getName();
            $sourceFile = new File($source, $name);
            $destinationFile = new File($destination, $name);

            if ($file->isDirectory()) {
                $this->handleDirectory($sourceFile, $destinationFile, $exclude);
                continue;
            }

            $extension = $file->getExtension();

            if (isset($this->extensionFileHandlers[$extension])) {
                $this->extensionFileHandlers[$extension]->handleFile($sourceFile, $destinationFile, $exclude);
            } else {
                $this->defaultFileHandler->handleFile($sourceFile, $destinationFile, $exclude);
            }
        }
    }

}