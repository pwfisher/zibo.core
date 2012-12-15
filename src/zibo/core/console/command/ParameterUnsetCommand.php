<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to unset a parameter
 */
class ParameterUnsetCommand extends ParameterCommand {

    /**
     * Constructs a new route register command
     * @return null
     */
    public function __construct() {
        parent::__construct('parameter unset', 'Unsets a parameter');
        $this->addArgument('key', 'Key of the parameter');
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
    	$key = $input->getArgument('key');

    	$this->zibo->setParameter($key, null);
    }

}