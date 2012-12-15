<?php

namespace zibo\core\console\exception;

/**
 * Exception thrown when a command didn't receive a required argument
 */
class ArgumentNotSetException extends ConsoleException {

    /**
     * Constructs a new argument not set exception
     * @param string $argument Name of the argument
     * @return null
     */
    public function __construct($argument) {
        parent::__construct('No ' . $argument . ' provided');
    }

}