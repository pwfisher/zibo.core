<?php

namespace zibo\core\build\handler;

use zibo\library\config\io\ini\IniParser;
use zibo\library\config\Config;
use zibo\library\filesystem\File;

/**
 * INI implementation of a FileHandler
 */
class ConfigIniFileHandler extends IniFileHandler {

    /**
     * INI parser
     * @var zibo\library\config\io\ini\IniParser
     */
    private $parser;

    /**
     * Constructs a new INI file handler
     * Enter description here ...
     */
    public function __construct() {
        $this->parser = new IniParser();
    }

    /**
     * Handles a file in the a Zibo module
     * @param zibo\library\filesystem\File $source The source file
     * @param zibo\library\filesystem\File $destination The destination
     * file
     * @param array $exclude Excluded file names as key of the array
     * @return null
     */
    public function handleFile(File $source, File $destination, array $exclude) {
        $ini = array();

        if ($destination->exists()) {
            $destinationIni = $destination->read();
            $this->parser->setIniString($ini, $destinationIni);
        }

        $sourceIni = $source->read();
        $this->parser->setIniString($ini, $sourceIni);

        $ini = Config::flattenConfig($ini);

        $this->write($destination, $ini);
    }

}