<?php

namespace zibo\core\build\handler;

use zibo\library\filesystem\File;

/**
 * Default copy implementation of a DirectoryHandler
 */
class DefaultDirectoryHandler implements DirectoryHandler {

    /**
     * Handles a directory in the a Zibo module
     * @param zibo\library\filesystem\File $source The source directory
     * @param zibo\library\filesystem\File $destination The destination
     * directory
     * @return null
     */
    public function handleDirectory(File $source, File $destination) {
        $files = $source->read();
        foreach ($files as $file) {
            $fileDestination = new File($destination, $file->getName());

            $file->copy($fileDestination);
        }
    }

}