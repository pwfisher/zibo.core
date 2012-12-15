<?php

namespace zibo\library\dependency\argument;

use zibo\library\dependency\DependencyCallArgument;
use zibo\library\Callback;

use \Exception;

/**
 * Parser to get a value through a call.
 */
class CallArgumentParser extends AbstractInjectableArgumentParser {

    /**
     * Name of the property for the class of the call
     * @var string
     */
    const PROPERTY_CLASS = 'class';

    /**
     * Name of the property for the method of the call
     * @var string
     */
    const PROPERTY_METHOD = 'method';

    /**
	 * Name of the property for the function of the call
     * @var string
     */
    const PROPERTY_FUNCTION = 'function';

	/**
	 * Gets the actual value of the argument
	 * @param zibo\library\dependency\DependencyCallArgument $argument The argument
	 * definition. The extra value of the argument is optional and can be used
	 * to define the id of the requested dependency
	 * @return mixed The value
	 */
	public function getValue(DependencyCallArgument $argument) {
	    $interface = $argument->getProperty(self::PROPERTY_INTERFACE);
	    $class = $argument->getProperty(self::PROPERTY_CLASS);
	    $function = $argument->getProperty(self::PROPERTY_FUNCTION);

	    if ($interface || $class) {
    	    if ($interface) {
    	        $id = $this->getDependencyId($argument);

    	        $object = $this->getDependency($interface, $id);
    	    } elseif ($class) {
    	        $object = $class;
    	    }

    	    $method = $argument->getProperty(self::PROPERTY_METHOD);
    	    if (!$method) {
    	        throw new Exception('Invalid argument properties, please define a method for your class or dependency');
    	    }

    	    $callback = new Callback(array($object, $method));
	    } elseif ($function) {
	        $callback = new Callback($function);
	    } else {
	        throw new Exception('Invalid argument properties, please define the interface, class or function property');
	    }

		return $callback->invoke();
	}

}