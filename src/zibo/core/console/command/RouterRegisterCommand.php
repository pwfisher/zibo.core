<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

use zibo\library\router\Route;
use zibo\library\Callback;

/**
 * Command to register a new route
 */
class RouterRegisterCommand extends AbstractCommand {

    /**
     * Constructs a new route register command
     * @return null
     */
    public function __construct() {
        parent::__construct('router register', 'Register a new route');
        $this->addArgument('path', 'Path of the route');
        $this->addArgument('controller', 'Class name of the controller');
        $this->addArgument('action', 'Action method (indexAction)', false);
        $this->addArgument('id', 'Id for the route', false);
        $this->addArgument('methods', 'Allowed methods for the route (eg get,head)', false);
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
    	$path = $input->getArgument('path');
    	$controller = $input->getArgument('controller');
    	$action = $input->getArgument('action', 'indexAction');
    	$id = $input->getArgument('id');
    	$alowedMethods = $input->getArgument('methods');

    	$callback = new Callback(array($controller, $action));

    	if ($allowedMethods) {
    		$allowedMethods = implode(',', $allowedMethods);
    	}

    	$route = new Route($path, $callback, $id, $allowedMethods);

    	$container = $this->zibo->getRouter()->getRouteContainer();
    	$container->addRoute($route);

    	$routerContainerIO = $this->zibo->getDependency('zibo\\core\\router\\RouterContainerIO');
    	$routerContainerIO->setRouteContainer($container);
    }

}