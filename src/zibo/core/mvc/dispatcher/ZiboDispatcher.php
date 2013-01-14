<?php

namespace zibo\core\mvc\dispatcher;

use zibo\library\mvc\exception\MvcException;

use zibo\library\dependency\DependencyCallArgument;

use zibo\core\mvc\controller\ZiboController;
use zibo\core\Zibo;

use zibo\library\mvc\controller\Controller;
use zibo\library\mvc\dispatcher\GenericDispatcher;
use zibo\library\mvc\Request;
use zibo\library\mvc\Response;
use zibo\library\router\Route;
use zibo\library\Callback;
use zibo\library\Value;

/**
 * Zibo dispatcher for the MVC implementation
 */
class ZiboDispatcher extends GenericDispatcher {

    /**
     * Separator between controller class name and the dependency id
     * @var string
     */
    const SEPARATOR_CONTROLLER_DEPENDENCY = '#';

    /**
     * The instance of Zibo
     * @var zibo\core\Zibo
     */
    protected $zibo;

    /**
     * Constructs a new Zibo dispatcher
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function __construct(Zibo $zibo) {
        $this->zibo = $zibo;
    }

    /**
     * Gets the controller of a request.
     * @param zibo\library\router\Route $route
     * @param zibo\library\mvc\controller\Controller $controller Result for the
     * controller of the action
     * @param string $action Result for the name of the method
     * @return null
     * @throws Exception when the controller could not be created
     */
    protected function getController(Route $route, &$controller, &$action) {
        $callback = $route->getCallback();

        if (!is_array($callback)) {
            return parent::getController($route, $controller, $action);
        }

        list($controller, $action) = $callback;
        if (!is_string($controller)) {
            return parent::getController($route, $controller, $action);
        }

        $positionColon = strpos($controller, self::SEPARATOR_CONTROLLER_DEPENDENCY);
        if ($positionColon !== false) {
            list($interface, $id) = explode(self::SEPARATOR_CONTROLLER_DEPENDENCY, $controller);

            $controller = $this->zibo->getDependency($interface, $id);

            $route->setCallback(array($controller, $action));
        }

        parent::getController($route, $controller, $action);
    }

    /**
     * Parses the arguments for the callback
     * @param Callback $callback
     * @param Route $route
     * @return array
     */
    protected function getArguments(Callback $callback, Route $route) {
        if ($route->isDynamic()) {
            return $route->getPredefinedArguments() + $route->getArguments();
        }

        $dependencyInjector = $this->zibo->getDependencyInjector();
        $argumentParsers = $dependencyInjector->getArgumentParsers();

        $arguments = $callback->getArguments();

        $routeArguments = $route->getArguments();
        $routePredefinedArguments = $route->getPredefinedArguments();

        foreach($arguments as $name => $type) {
            if (isset($routeArguments[$name])) {
                $arguments[$name] = urldecode($routeArguments[$name]);
            } elseif (isset($routePredefinedArguments[$name])) {
                $argument = $routePredefinedArguments[$name];

                if ($argument instanceof DependencyCallArgument) {
                    $type = $argument->getType();
                    if (!isset($argumentParsers[$type])) {
                        throw new MvcException('No parser found for argument $' . $argument->getName() . ' with type ' . $type);
                    }

                    $argument = $argumentParsers[$type]->getValue($argument);
                }

                $arguments[$name] = $argument;
            } elseif ($type) {
                $arguments[$name] = $dependencyInjector->get($type);
            }
        }

        return $arguments;
    }

    /**
     * Prepares the controller
     * @param Request $request The request for the controller
     * @param Response $response The response for the controller
     * @param zibo\library\mvc\controller\Controller $controller The controller to prepare
     * @param string $action The name of the action method which will be invoked
     * @param array $arguments The arguments for the action method
     * @return null
     */
    protected function prepareController(Request $request, Response $response, Controller $controller, $action, array $arguments) {
        parent::prepareController($request, $response, $controller, $action, $arguments);

        if ($controller instanceof ZiboController) {
            $controller->setZibo($this->zibo);
        }
    }

    /**
     * Invokes the controller
     * @param zibo\library\Callback $callback Callback to the action of the
     * controller
     * @param array $arguments Arguments for the action
     * @return mixed Return value of the action
     */
    protected function invoke(Callback $callback, array $arguments) {
        $log = $this->zibo->getLog();
        if (!$log) {
            return parent::invoke($callback, $arguments);
        }

        $returnValue = null;
        $controller = $callback->getClass();

        $value = new Value();

        $actionArguments = array();
        foreach ($arguments as $argument) {
            $actionArguments[] = $value->toString($argument);
        }

        $controllerClass = get_class($controller);
        $action = $callback->getMethod();
        $action .= '(' . implode(', ', $actionArguments) . ')';

        $log->logDebug('Invoking ' . $controllerClass. '->preAction()', null, Zibo::LOG_SOURCE);
        if ($controller->preAction()) {
            $log->logDebug('Invoking ' . $controllerClass. '->' . $action, null, Zibo::LOG_SOURCE);
            $returnValue = $callback->invokeWithArrayArguments($arguments);

            $log->logDebug('Invoking ' . $controllerClass. '->postAction()', null, Zibo::LOG_SOURCE);
            $controller->postAction();
        } else {
            $log->logDebug('Skipping ' . $controllerClass. '->' . $action, 'preAction returned false', Zibo::LOG_SOURCE);
        }

        return $returnValue;
    }

}