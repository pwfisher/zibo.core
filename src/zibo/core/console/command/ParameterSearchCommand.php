<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

use zibo\library\config\Config;

/**
 * Command to search for parameters
 */
class ParameterSearchCommand extends AbstractCommand {

    /**
     * Constructs a new route search command
     * @return null
     */
    public function __construct() {
        parent::__construct('parameter', 'Show an overview of the defined parameters');
        $this->addArgument('query', 'Query to search the parameters', false, true);
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
    	$config = $this->zibo->getEnvironment()->getConfig();

    	$values = $config->getAll();
    	$values = Config::flattenConfig($values);

    	$query = $input->getArgument('query');
    	if ($query) {
    		foreach ($values as $key => $value) {
    			if (stripos($key, $query) !== false) {
    				continue;
    			}

    			if (stripos($value, $query) !== false) {
    				continue;
    			}

    			unset($values[$key]);
    		}
    	}

    	ksort($values);

    	foreach ($values as $key => $value) {
    		$output->write($key . ' = ' . $value);
    	}
    }

}