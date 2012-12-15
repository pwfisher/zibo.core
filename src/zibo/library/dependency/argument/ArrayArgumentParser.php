<?php

namespace zibo\library\dependency\argument;

use zibo\library\dependency\DependencyCallArgument;

/**
 * Parser for array values
 */
class ArrayArgumentParser implements ArgumentParser {

	/**
	 * Gets the actual value of the argument
	 * @param zibo\library\dependency\DependencyCallArgument $argument The argument definition
	 * @return mixed The value
	 */
	public function getValue(DependencyCallArgument $argument) {
		return $argument->getProperties();
	}

}