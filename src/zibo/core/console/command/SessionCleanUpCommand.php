<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to clean up the invalidated sessions
 */
class SessionCleanUpCommand extends AbstractCommand {

    /**
     * Constructs a new session clean up command
     * @return null
     */
    public function __construct() {
        parent::__construct('session clean', 'Cleans up the invalidated sessions');
        $this->addFlag('force', 'To clear all sessions');
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $force = $input->hasFlag('force');

    	$sessionIO = $this->zibo->getDependency('zibo\\library\\http\\session\\io\\SessionIO');
    	$sessionIO->clean($force);
    }

}