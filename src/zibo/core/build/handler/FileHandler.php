<?php

namespace zibo\core\build\handler;

use zibo\library\filesystem\File;

/**
 * Interface to handle a specific type of files
 */
interface FileHandler {

    /**
     * Handles a file
     * @param zibo\library\filesystem\File $source The source file
     * @param zibo\library\filesystem\File $destination The destination
     * file
     * @return null
     */
    public function handleFile(File $source, File $destination);

}