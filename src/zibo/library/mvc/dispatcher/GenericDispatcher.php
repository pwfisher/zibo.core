<?php

namespace zibo\library\mvc\dispatcher;

use zibo\library\mvc\controller\Controller;
use zibo\library\mvc\exception\MvcException;
use zibo\library\mvc\Request;
use zibo\library\mvc\Response;
use zibo\library\router\Route;

use zibo\library\Callback;

use \Exception;

/**
 * Generic dispatcher for request objects
 */
class GenericDispatcher implements Dispatcher {

    /**
     * Dispatches a request to the action of a controller
     * @param zibo\library\mvc\Request $request The request to dispatch
     * @param zibo\library\mvc\Response $response The response to dispatch the request to
     * @return mixed The return value of the action
     * @throws Exception when the action is not invokable
     */
    public function dispatch(Request $request, Response $response) {
        $returnValue = null;

        $controller = null;
        $action = null;

        // get the controller
        $route = $request->getRoute();
        $this->getController($route, $controller, $action);

        // translate the action/arguments into a method
        $callback = $this->getCallback($controller, $action);
        $arguments = $this->getArguments($callback, $route);

        // prepare the controller by setting the request/response to it
        $this->prepareController($request, $response, $controller, $action, $arguments);

        // invoke the action
        $this->preInvoke($controller, $action, $arguments);
        $returnValue = $this->invoke($callback, $arguments);
        $this->postInvoke($controller, $action, $arguments);

        return $returnValue;
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
        $callback = new Callback($route->getCallback());

        $controller = $callback->getClass();
        if (!$controller) {
            throw new MvcException('No controller class provided in the route');
        }

        if (is_string($controller)) {
            $controller = new $controller;
        }

        if (!$controller instanceof Controller) {
            throw new MvcException(get_class($controller) . ' does not implement zibo\\library\\mvc\\controller\\Controller');
        }

        $action = $callback->getMethod();
    }

    /**
     * Gets the callback for the provided method
     * @param mixed $controller Controller of the action method
     * @param string $methodName Name of the action method
     * @return zibo\library\Callback
     * @throws zibo\ZiboException when the method is not invokable
     */
    protected function getCallback($controller, $methodName) {
        try {
            $callback = new Callback(array($controller, $methodName));
            if (!$callback->isCallable()) {
                throw new Exception('Could not invoke action ' . $methodName . ' in ' . get_class($controller));
            }
        } catch (Exception $exception) {
            throw new Exception('Could not dispatch action ' . $methodName . ' in ' . get_class($controller), 0, $exception);
        }

        return $callback;
    }

    /**
     * Parses the arguments for the callback
     * @param Callback $callback
     * @param Route $route
     * @return array
     */
    protected function getArguments(Callback $callback, Route $route) {
        if ($route->isDynamic()) {
            return $route->getArguments();
        }

        $arguments = $callback->getArguments();

        $routeArguments = $route->getArguments();
        $routePredefinedArguments = $route->getPredefinedArguments();

        foreach($arguments as $name => $type) {
            if (isset($routeArguments[$name])) {
                $arguments[$name] = urldecode($routeArguments[$name]);
            } elseif (isset($routePredefinedArguments[$name])) {
                $arguments[$name] = $routePredefinedArguments[$name];
            } elseif ($type) {
                $arguments[$name] = null;
            }
        }

        return $arguments;
    }

    /**
     * Prepares the controller
     * @param Request $request The request for the controller
     * @param Response $response The response for the controller
     * @param zibo\library\mvc\controller\Controller $controller The controller
     * to prepare
     * @param string $actionName The action method which will be invoked
     * @param array $arguments The arguments for the action method
     * @return null
     */
    protected function prepareController(Request $request, Response $response, Controller $controller, $actionName, array $arguments) {
        $controller->setRequest($request);
        $controller->setResponse($response);
    }

    /**
     * Hook straight before invoking the controller
     * @param zibo\library\mvc\controller\Controller $controller The controller
     * to dispatch
     * @param string $actionName The action method which will be invoked
     * @param array $arguments The arguments for the action method
     * @return null
     */
    protected function preInvoke(Controller $controller, $actionName, array $arguments) {

    }

    /**
     * Invokes the controller
     * @param zibo\library\Callback $callback Callback to the action of the
     * controller
     * @param array $arguments Arguments for the action
     * @return mixed Return value of the action
     */
    protected function invoke(Callback $callback, array $arguments) {
        $returnValue = null;
        $controller = $callback->getClass();

        if ($controller->preAction()) {
            $returnValue = $callback->invokeWithArrayArguments($arguments);
            $controller->postAction();
        }

        return $returnValue;
    }

    /**
     * Hook straight after invoking the controller
     * @param zibo\library\mvc\controller\Controller $controller The controller
     * to dispatch
     * @param string $actionName The action method which will be invoked
     * @param array $arguments The arguments for the action method
     * @return null
     */
    protected function postInvoke(Controller $controller, $actionName, array $arguments) {

    }

}