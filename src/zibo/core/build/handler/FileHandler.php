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
     * @param array $exclude Excluded file names as key of the array
     * @param array $exclude Excluded file names as key of the array
     * @return null
     */
    public function handleFile(File $source, File $destination, array $exclude);

}