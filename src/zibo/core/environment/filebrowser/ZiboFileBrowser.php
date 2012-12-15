<?php

namespace zibo\core\environment\filebrowser;

use zibo\library\filesystem\exception\FileSystemException;
use zibo\library\filesystem\File;

/**
 * Generic browser to find files in the Zibo filesystem structure
 */
class ZiboFileBrowser extends AbstractFileBrowser {

    /**
     * Look for files by looping through the include paths
     * @param string $fileName relative path of a file in the Zibo filesystem structure
     * @param boolean $firstOnly true to get the first matched file, false to get an array
     *                           with all the matched files
     * @return zibo\library\filesystem\File|array Depending on the firstOnly
     * flag, an instance of zibo\library\filesystem\File or an array
     * @throws zibo\library\filesystem\exception\FileSystemException when
     * $fileName is empty or not a string
     */
    protected function lookupFile($fileName, $firstOnly) {
        if (!($fileName instanceof File) && (!is_string($fileName) || !$fileName)) {
            throw new FileSystemException('Provided filename is empty');
        }

        $files = array();

        $includeDirectories = $this->getIncludeDirectories();
        foreach ($includeDirectories as $includeDirectory) {
            $file = new File($includeDirectory, $fileName);

            if (!$file->exists()) {
                continue;
            }

            if ($firstOnly) {
                return $file;
            }


            $files[$file->getPath()] = $file;
        }

        if ($firstOnly) {
            return null;
        }

        return $files;
    }

}