<?php

namespace zibo\library\dependency\argument;

use zibo\library\dependency\DependencyCallArgument;

/**
 * Parser for defined dependency values.
 */
class DependencyArgumentParser extends AbstractInjectableArgumentParser {

	/**
	 * Gets the actual value of the argument
	 * @param zibo\library\dependency\DependencyCallArgument $argument The argument
	 * definition. The extra value of the argument is optional and can be used
	 * to define the id of the requested dependency
	 * @return mixed The value
	 */
	public function getValue(DependencyCallArgument $argument) {
	    $interface = $argument->getProperty(self::PROPERTY_INTERFACE);
        $id = $this->getDependencyId($argument);

        return $this->getDependency($interface, $id);
	}

}