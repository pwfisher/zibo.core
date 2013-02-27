<?php

namespace zibo\core\build\handler;

use zibo\core\build\exception\BuildException;

use zibo\library\filesystem\File;

/**
 * INI implementation of a FileHandler
 */
class IniFileHandler implements FileHandler {

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
            $ini = $this->read($destination);
        }

        if (!isset($ini) || $ini === false) {
            $ini = array();
        }

        $sourceIni = $this->read($source);
        if ($sourceIni === false) {
            throw new BuildException('Could not read ' . $source);
        }

        foreach ($sourceIni as $key => $value) {
            $ini[$key] = $value;
        }

        $this->write($destination, $ini);
    }

    /**
     * Reads the ini file
     * @param zibo\library\filesystem\File $file
     * @return array Ini contents as array
     */
    protected function read(File $file) {
        return parse_ini_file($file->getPath(), false, INI_SCANNER_RAW);
    }

    /**
     * Writes the ini to file
     * @param zibo\library\filesystem\File $file
     * @param array $ini
     * @return null
     */
    protected function write(File $file, array $ini) {
        ksort($ini);

        $output = '';
        foreach ($ini as $key => $value) {
            $value = trim($value);

            if (!(substr($value, 0, 1) == '"' && substr($value, -1) == '"')) {
                $value = '"' . $value . '"';
            }

            $value = str_replace('"', '\\"', substr($value, 1, -1));
            $value = '"' . str_replace('\\\\"', '\\"', $value) . '"';

            $output .= $key . ' = ' . $value . '' . "\n";
        }

        $parent = $file->getParent();
        $parent->create();

        $file->write($output);
    }

}