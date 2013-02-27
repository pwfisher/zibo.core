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
     * @param array $exclude Excluded file names as key of the array
     * @return null
     */
    public function handleDirectory(File $source, File $destination, array $exclude) {
        $files = $source->read();
        foreach ($files as $file) {
//             echo $file;

            foreach ($exclude as $pattern => $null) {
                if (strpos($file->getAbsolutePath(), $pattern) !== false) {
//                     echo " - skip\n";
                    continue 2;
                }
            }

            $fileDestination = new File($destination, $file->getName());

            if ($file->isDirectory()) {
//                 echo " - recursive\n";
                $this->handleDirectory($file, $fileDestination, $exclude);
            } else {
//                 echo " - copy\n";
                $file->copy($fileDestination);
            }
        }
    }

}