<?php

namespace zibo\core\console\exception;

/**
 * Exception thrown when a command received an invalid number of arguments
 */
class InvalidArgumentCountException extends ConsoleException {

    /**
     * Constructs a new command not found exception
     * @param string $command The command
     * @return null
     */
    public function __construct() {
        parent::__construct('Invalid argument count');
    }

}