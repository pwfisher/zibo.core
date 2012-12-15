<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to route a path
 */
class RouterRouteCommand extends AbstractCommand {

    /**
     * Constructs a new route search command
     * @return null
     */
    public function __construct() {
        parent::__construct('router route', 'Routes the provided path');
        $this->addArgument('path', 'Path to route', false);
        $this->addArgument('method', 'Method of the request', false);
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
    	$path = $input->getArgument('path', '/');
    	$method = $input->getArgument('method', 'GET');

    	$router = $this->zibo->getRouter();
    	$routerResult = $router->route($method, $path);

    	if (!$routerResult->isEmpty()) {
    		$route = $routerResult->getRoute();
    		if ($route) {
    			$output->write('200 Ok');
    			$output->write($route);
    		} else {
    			$allowedMethods = $routerResult->getAllowedMethods();

    			$output->write('405 Method not allowed');
    			$output->write('Allow: ' . implode(', ', $allowedMethods));
    		}
    	} else {
    		$output->write('404 Not found');
    	}
    }

}