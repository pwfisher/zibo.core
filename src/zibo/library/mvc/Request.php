<?php

namespace zibo\library\mvc;

use zibo\library\http\Request as HttpRequest;
use zibo\library\router\Route;

/**
 * A extension of the HTTP request with route and URL functionality
 */
class Request extends HttpRequest {

    /**
     * The selected route
     * @var zibo\library\router\Route
     */
    protected $route;

    /**
     * Constructs a new request
     * @param zibo\library\http\Request $request A HTTP request
     * @param zibo\library\router\Route $route The selected route
     * @return null
     */
    public function __construct(HttpRequest $request, Route $route) {
        $this->method = $request->method;
        $this->path = $request->path;
        $this->baseUrl = $request->baseUrl;
        $this->basePath = $request->basePath;
        $this->baseScript = $request->baseScript;
        $this->queryParameters = $request->queryParameters;
        $this->protocol = $request->protocol;
        $this->headers = $request->headers;
        $this->cookies = $request->cookies;
        $this->body = $request->body;
        $this->bodyParameters = $request->bodyParameters;

        $this->route = $route;
    }

    /**
     * Gets the properties to be serialized
     * @return array Array with property names
     */
    public function __sleep() {
        $properties = parent::__sleep();
        $properties[] = 'route';

        return $properties;
    }

    /**
     * Gets the requested path from the baseScript
     * @return string
     */
    public function getRoutePath() {
        if ($this->route->isDynamic()) {
            return $this->route->getPath();
        }

        $baseUrl = $this->getBaseUrl();
        $baseScript = $this->getBaseScript();
        $script = str_replace($baseUrl, '', $baseScript);

        $path = substr($this->basePath, strlen($script));

        return $path;
    }

    /**
     * Gets the URL of the requested route, this is the base URL with the
     * path of the selected route
     * @return string
     */
    public function getRouteUrl() {
        return $this->getBaseScript() . $this->getRoutePath();
    }

    /**
     * Gets the selected route
     * @return zibo\library\router\Route
     */
    public function getRoute() {
        return $this->route;
    }

}