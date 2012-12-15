<?php

namespace zibo\core\environment\dependency\argument;

use zibo\library\config\Config;
use zibo\library\dependency\argument\DependencyArgumentParser as LibDependencyArgumentParser;
use zibo\library\dependency\DependencyCallArgument;
use zibo\library\Callback;

use \Exception;

/**
 * Parser for defined dependency values.
 */
class DependencyArgumentParser extends LibDependencyArgumentParser {

    /**
     * Delimiter for a parameter value
     * @var string
     */
    const DELIMITER = '%';

    /**
     * Instance of the configuration
     * @var zibo\library\config\Config
     */
    private $config;

    /**
     * Constructs a new dependency argument parser
     * @param zibo\library\dependency\DependencyInjector $di
     * @return null
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Gets the id of the dependency
     * @param zibo\library\dependency\DependencyCallArgument $argument
     * @return string|null
     */
    protected function getDependencyId(DependencyCallArgument $argument) {
        $id = $argument->getProperty(self::PROPERTY_ID);
        $id = self::processDependencyId($id, $this->config);

        return $id;
    }

    /**
     * Processes the id as a Zibo parameter if it's delimited by the parameter
     * delimiter
     * @param string|null $id A dependency id
     * @param zibo\library\config\Config $config Instance of the parameter
     * configuration
     * @return string|null
     */
    public static function processDependencyId($id, Config $config) {
        if (!$id) {
            return null;
        }

        if (substr($id, 0, 1) != self::DELIMITER || substr($id, -1) != self::DELIMITER) {
            return $id;
        }

        return $config->get(substr($id, 1, -1));
    }

}