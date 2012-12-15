<?php

namespace zibo\library\router;

use zibo\library\router\exception\RouterException;

/**
 * Generic router implementation
 */
class GenericRouter implements Router {

    /**
     * A route container with the defined routes
     * @var RouteContainer
     */
    private $routeContainer;

    /**
     * Full class name of the default controller
     * @var string
     */
    private $defaultController;

    /**
     * Name of the default action method
     * @var string
     */
    private $defaultAction;

    /**
     * The matcher for the routes
     * @var RouteMatcher
     */
    private $matcher;

    /**
     * Construct a new router
     * @param RouteContainer $io Route I/O implementation to use
     * @return null
     */
    public function __construct(RouteContainer $routeContainer, RouteMatcher $routeMatcher) {
        $this->routeContainer = $routeContainer;
        $this->matcher = $routeMatcher;

        $defaultController = null;
        $defaultAction = null;
    }

    /**
     * Sets the default action of this router
     * @param string $defaultController full class name of the default controller
     * @param string $defaultAction method name of the default action in the controller
     * @return null
     * @throws zibo\library\router\exception\RouterException when the default
     * controller is an invalid or empty value
     * @throws zibo\library\router\exception\RouterException when the default
     * action is an invalid or empty value
     */
    public function setDefaultAction($defaultController, $defaultAction = null) {
        if (!is_string($defaultController) || !$defaultController) {
            throw new RouterException('Provided default controller is empty or not a string');
        }

        if ($defaultAction !== null && (!is_string($defaultAction) || !$defaultAction)) {
            throw new RouterException('Provided default action is empty or not a string');
        }

        if ($defaultAction === null) {
            $defaultAction = '*';
        }

        $this->defaultController = $defaultController;
        $this->defaultAction = $defaultAction;
    }

    /**
     * Gets the default controller
     * @return string full class name of the default controller
     */
    public function getDefaultController() {
        return $this->defaultController;
    }

    /**
     * Gets the default action
     * @return string method name of the default action
     */
    public function getDefaultAction() {
        return $this->defaultAction;
    }

    /**
     * Gets the route container
     * @return RouteContainer
     */
    public function getRouteContainer() {
        return $this->routeContainer;
    }

    /**
     * Routes the request path to a Route object
     * @param string $method The requested method
     * @param string $path The requested path
     * @param string $baseUrl The base URL
     * @return RouterResult
     */
    public function route($method, $path, $baseUrl) {
        $path = $this->processPath($path);

        $result = $this->getRouteFromPath($method, $path, $baseUrl, $this->routeContainer->getRoutes());
        if (!$result->isEmpty()) {
        	return $result;
        }

        if ($this->defaultController && $path == '/') {
            $route = new Route($path, array($this->defaultController, $this->defaultAction));

            $result->setRoute($route);
        }

        return $result;
    }

    /**
     * Gets a route from the route definitions for the requested path
     * @param string $method The requested method
     * @param string $path The requested path
     * @param array $routes The available routes
     * @return RouterResult
     */
    protected function getRouteFromPath($method, $path, $baseUrl, array $routes) {
        $result = new RouterResult();

        $route = $this->matcher->matchRoute($method, $path, $routes, $baseUrl);
        if ($route === null) {
        	return $result;
        }

        if (!$route->isMethodAllowed($method)) {
            $allowedMethods = array_keys($route->getAllowedMethods());
            $result->setAllowedMethods($allowedMethods);
        } else {
            $result->setRoute($route);
        }

        return $result;
    }

    /**
     * Removes the base path to the script and the query arguments from the
     * provided path
     * @param string $path The requested path, this will be trimmed from the
     * base path and the query string
     * @return string The processed path
     */
    protected function processPath($path) {
        $positionPhp = strpos($path, '.php');
        if ($positionPhp !== false) {
            $positionPhp += 4;
            $path = substr($path, $positionPhp);
        }

        $positionQuestion = strpos($path, '?');
        if ($positionQuestion !== false) {
            $path = substr($path, 0, $positionQuestion);
        }

        if ($path != '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

}