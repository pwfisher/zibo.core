<?php

namespace zibo\core\build\handler;

use zibo\library\filesystem\File;

/**
 * Default copy implementation of a FileHandler
 */
class DefaultFileHandler implements FileHandler {

    /**
     * Handles a file in the a Zibo module
     * @param zibo\library\filesystem\File $source The source file
     * @param zibo\library\filesystem\File $destination The destination
     * file
     * @return null
     */
    public function handleFile(File $source, File $destination) {
        $file->copy($destination);
    }

}