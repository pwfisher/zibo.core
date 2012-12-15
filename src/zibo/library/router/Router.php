<?php

namespace zibo\library\router;

/**
 * A router maps a URL path to a controller class and action method.
 */
interface Router {

    /**
     * Routes the request path to a Route object
     * @param string $method The requested method
     * @param string $path The requested path
     * @param string $baseUrl The base URL
     * @return RouterResult
     */
    public function route($method, $path, $baseUrl);

    /**
     * Gets the route container
     * @return RouteContainer A route container
     */
    public function getRouteContainer();

}