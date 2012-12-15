<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to search for routes
 */
class RouterSearchCommand extends AbstractCommand {

    /**
     * Constructs a new route search command
     * @return null
     */
    public function __construct() {
        parent::__construct('router', 'Show an overview of the defined routes');
        $this->addArgument('query', 'Query to search the routes', false, true);
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $router = $this->zibo->getRouter();
        $routeContainer = $router->getRouteContainer();
        $routes = $routeContainer->getRoutes();

        $query = $input->getArgument('query');
        if ($query) {
            foreach ($routes as $id => $route) {
                if (stripos($route->getPath(), $query) !== false) {
                    continue;
                }

                unset($routes[$id]);
            }
        }

        ksort($routes);

        foreach ($routes as $route) {
            $output->write($route);
        }
    }

}