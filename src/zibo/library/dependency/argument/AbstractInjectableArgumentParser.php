<?php

namespace zibo\library\dependency\argument;

use zibo\library\dependency\DependencyCallArgument;
use zibo\library\dependency\DependencyInjector;

/**
 * Parser for defined dependency values.
 */
abstract class AbstractInjectableArgumentParser implements InjectableArgumentParser {

    /**
     * Name of the property for the interface of the dependency
     * @var string
     */
    const PROPERTY_INTERFACE = 'interface';

    /**
     * Name of the property for the id of the dependency
     * @var string
     */
    const PROPERTY_ID = 'id';

	/**
	 * Instance of the dependency injector
	 * @var zibo\library\dependency\DependencyInjector
	 */
	protected $di;

	/**
	 * Exclusion list for the dependency injector
	 * @var array
	 */
	protected $exclude;

	/**
	 * Sets the dependency injector to this parser
	 * @param zibo\library\dependency\DependencyInjector $di
	 * @return null
	 */
	public function setDependencyInjector(DependencyInjector $di) {
		$this->di = $di;
	}

	/**
	 * Sets the exclude array of the dependency injector
	 * @param array $exclude
	 * @return null
	 */
	public function setExclude(array $exclude = null) {
		$this->exclude = $exclude;
	}

	/**
	 * Gets the dependency
	 * @param string $interface Name of the interface
	 * @param string|null $id The id of the instance
	 * @return mixed
	 */
	protected function getDependency($interface, $id) {
        if ($id) {
	        return $this->di->get($interface, $id);
        }

        return $this->di->get($interface, null, null, $this->exclude);
	}

	/**
	 * Gets the id of the dependency
	 * @param zibo\library\dependency\DependencyCallArgument $argument
	 * @return string|null
	 */
	protected function getDependencyId(DependencyCallArgument $argument) {
        return $argument->getProperty(self::PROPERTY_ID);
	}

}