<?php

namespace zibo\core\build\handler;

use zibo\library\filesystem\File;

/**
 * Interface to handle a specific directory of a Zibo module when building
 * your installation to a production environment
 */
interface DirectoryHandler {

    /**
     * Handles a directory in the a Zibo module
     * @param zibo\library\filesystem\File $source The source directory
     * @param zibo\library\filesystem\File $destination The destination
     * directory
     * @return null
     */
    public function handleDirectory(File $source, File $destination);

}