<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to get a parameter
 */
class ParameterGetCommand extends ParameterCommand {

    /**
     * Constructs a new parameter get command
     * @return null
     */
    public function __construct() {
        parent::__construct('parameter get', 'Gets the value of a parameter');
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

    	$value = $this->zibo->getParameter($key);

    	$output->write(var_export($value, true));
    }

}