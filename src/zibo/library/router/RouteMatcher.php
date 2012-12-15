<?php

namespace zibo\library\router;

/**
 * Matcher for routes
 */
class RouteMatcher {

    /**
     * Matches a route
     * @param string $method The method of the request
     * @param string $path The path of the request
     * @param array $routes The available routes
     * @return Route|null A route if matched, null otherwise
     */
    public function matchRoute($method, $path, array $routes, $baseUrl = null) {
        $pathTokens = Route::tokenizePath($path);

        $result = null;
        $resultArguments = null;
        $numResultTokens = -1;

        foreach ($routes as $route) {
            $routeTokens = Route::tokenizePath($route->getPath());

            $arguments = $this->matchTokens($pathTokens, $routeTokens, $route->isDynamic());
            if ($arguments === false) {
                continue;
            }

            $numRouteTokens = count($routeTokens);
            if ($numRouteTokens < $numResultTokens) {
                continue;
            }

            if ($result && (!$route->isMethodAllowed($method) || count($resultArguments) < count($arguments))) {
                continue;
            }

            $routeBaseUrl = $route->getBaseUrl();
            if ($baseUrl && $routeBaseUrl && $routeBaseUrl != $baseUrl) {
                continue;
            }

            $result = $route;
            $resultArguments = $arguments;
            $numResultTokens = $numRouteTokens;
        }

        if (!$result) {
            return null;
        }

        $route = clone $result;
        $route->setArguments($resultArguments);

        return $route;
    }

    /**
     * Matches the tokens of the path with the tokens of a route
     * @param array $pathTokens Tokens of the path
     * @param array $routeTokens Tokens of a route
     * @param boolean $isDynamic Flag to see if it is a dynamic route
     * @return boolean|array False when no match, an array when matched with
     * the matched arguments
     */
    private function matchTokens(array $pathTokens, array $routeTokens, $isDynamic) {
        $arguments = array();

        $numPathTokens = count($pathTokens);
        $numRouteTokens = count($routeTokens);

        if ($numPathTokens < $numRouteTokens) {
            return false;
        }

        foreach ($routeTokens as $index => $routeToken) {
            $parameterName = substr($routeToken, 1, -1);
            $isParameter = $routeToken == '%' . $parameterName . '%';

            if ($isParameter) {
                $arguments[$parameterName] = $pathTokens[$index];
                continue;
            }

            if ($routeToken != $pathTokens[$index]) {
                return false;
            }
        }

        if (!$isDynamic) {
            if ($numPathTokens != $numRouteTokens) {
                return false;
            }

            return $arguments;
        }

        $index = $numRouteTokens;
        while (isset($pathTokens[$index])) {
            $arguments[] = $pathTokens[$index];
            $index++;
        }

        return $arguments;
    }

}