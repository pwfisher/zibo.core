<?php

namespace zibo\core\environment\dependency\argument;

use zibo\library\config\Config;
use zibo\library\dependency\argument\ArgumentParser;
use zibo\library\dependency\exception\DependencyException;
use zibo\library\dependency\DependencyCallArgument;

/**
 * Parser for defined parameters.
 */
class ConfigArgumentParser implements ArgumentParser {

    /**
     * Name of the property for the key of a parameter
     * @var string
     */
    const PROPERTY_KEY = 'key';

    /**
     * Name of the property for the default value of a parameter
     * @var string
     */
    const PROPERTY_DEFAULT = 'default';

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
	 * Gets the actual value of the argument
	 * @param zibo\library\dependency\DependencyCallArgument $argument
	 * @return mixed The value
	 */
	public function getValue(DependencyCallArgument $argument) {
	    $key = $argument->getProperty(self::PROPERTY_KEY);
	    $default = $argument->getProperty(self::PROPERTY_DEFAULT);

	    if (!$key) {
	        throw new DependencyException('No key property set for argument $' . $argument->getName());
	    }


		return $this->config->get($key, $default);
	}

}