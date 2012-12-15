<?php

namespace zibo\library\dependency\argument;

use zibo\library\dependency\DependencyInjector;

/**
 * Parser for defined dependency values.
 */
interface InjectableArgumentParser extends ArgumentParser {

	/**
	 * Sets the dependency injector to this parser
	 * @param zibo\library\dependency\DependencyInjector $di
	 * @return null
	 */
	public function setDependencyInjector(DependencyInjector $di);

	/**
	 * Sets the exclude array of the dependency injector
	 * @param array $exclude
	 * @return null
	 */
	public function setExclude(array $exclude = null);

}