<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to unregister a route
 */
class RouterUnregisterCommand extends AbstractCommand {

    /**
     * Constructs a new route unregister command
     * @return null
     */
    public function __construct() {
        parent::__construct('router unregister', 'Unregister a route');
        $this->addArgument('id', 'Id of the route');
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
    	$id = $input->getArgument('id');

    	$container = $this->zibo->getRouter()->getRouteContainer();
    	$container->removeRouteById($id);

    	$routerContainerIO = $this->zibo->getDependency('zibo\\core\\router\\RouterContainerIO');
    	$routerContainerIO->setRouteContainer($container);
    }

}