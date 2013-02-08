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
     * @return null
     */
    public function handleFile(File $source, File $destination) {
        if ($destination->exists()) {
            $ini = parse_ini_file($destination->getPath(), false, INI_SCANNER_RAW);
        }

        if (!isset($ini) || $ini === false) {
            $ini = array();
        }

        $sourceIni = parse_ini_file($source->getPath(), false, INI_SCANNER_RAW);
        if ($sourceIni === false) {
            throw new BuildException('Could not read ' . $source);
        }

        foreach ($sourceIni as $key => $value) {
            $ini[$key] = $value;
        }

        $this->write($destination, $ini);
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
            $value = str_replace('"', '\\"', $value);

            if (!(substr($value, 0, 1) == '"' && substr($value, -1) == '"')) {
                $value = '"' . $value . '"';
            }

            $output .= $key . ' = ' . $value . '' . "\n";
        }

        $parent = $file->getParent();
        $parent->create();

        $file->write($output);
    }

}