<?php

namespace zibo\core\build\handler;

use zibo\library\filesystem\File;

/**
 * Conf implementation of a FileHandler
 */
class ConfFileHandler implements FileHandler {

    /**
     * Handles a file in the a Zibo module
     * @param zibo\library\filesystem\File $source The source file
     * @param zibo\library\filesystem\File $destination The destination
     * file
     * @param array $exclude Excluded file names as key of the array
     * @return null
     */
    public function handleFile(File $source, File $destination, array $exclude) {
        if ($destination->exists()) {
            $conf = $destination->read();
        } else {
            $conf = '';
        }

        $conf .= "\n" . $source->read();

        $destination->write($conf);
    }

}