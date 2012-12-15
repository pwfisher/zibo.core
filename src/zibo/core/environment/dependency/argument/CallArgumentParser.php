<?php

namespace zibo\core\environment\dependency\argument;

use zibo\library\config\Config;
use zibo\library\dependency\argument\CallArgumentParser as LibCallArgumentParser;
use zibo\library\dependency\DependencyCallArgument;
use zibo\library\Callback;

use \Exception;

/**
 * Parser to get a value through a call.
 */
class CallArgumentParser extends LibCallArgumentParser {

    /**
     * Instance of the configuration
     * @var zibo\library\config\Config
     */
    private $config;

    /**
     * Constructs a new call argument parser
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
        $id = DependencyArgumentParser::processDependencyId($id, $this->config);

        return $id;
    }

}