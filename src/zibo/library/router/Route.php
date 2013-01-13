<?php

namespace zibo\library\router;

use zibo\library\router\exception\RouterException;

/**
 * Data container for the definition of a controller action
 */
class Route {

    /**
     * Base URL to the system
     * @var string
     */
    private $baseUrl;

    /**
     * URL path to the controller
     * @var string
     */
    private $path;

    /**
     * Flag to see if this route has dynamic arguments
     * @var boolean
     */
    private $isDynamic;

    /**
     * The callback for this route
     * @var string|array
     */
    private $callback;

    /**
     * The arguments for the action method
     * @var array|null
     */
    private $arguments;

    /**
     * Predefined arguments for the callback
     * @var array|null
     */
    private $predefinedArguments;

    /**
     * The allowed methods for this route
     * @var array|null
     */
    private $allowedMethods;

    /**
     * The id of this route
     * @var string
     */
    private $id;

    /**
     * The locale code for this route
     * @var string
     */
    private $locale;

    /**
     * Constructs a new route
     * @param string $path URL path to the controller
     * @param string|array $callback Callback to the action of this route
     * @param string $id The id of this route
     * @param string|array|null $allowedMethods The allowed methods for this
     * route
     * @return null
     */
    public function __construct($path, $callback, $id = null, $allowedMethods = null) {
        $this->setPath($path);
        $this->setCallback($callback);
        $this->setId($id);
        $this->setAllowedMethods($allowedMethods);
        $this->setIsDynamic(false);
        $this->setArguments(null);
        $this->setPredefinedArguments(null);
        $this->setLocale(null);
        $this->setBaseUrl(null);
    }

    /**
     * Gets a string representation of this route
     * @return string
     */
    public function __toString() {
        $string = $this->path . ' ';

        if (is_array($this->callback) && count($this->callback) == 2 && isset($this->callback[0])) {
            $string .= $this->callback[0] . '->' . $this->callback[1];
        } else {
            $string .= $this->callback;
        }

        if ($this->arguments) {
            $string .= '(' . implode(', ', $this->arguments) . ')';
        } elseif ($this->predefinedArguments) {
            $string .= '(' . implode(', ', $this->predefinedArguments) . ')';
        } else {
            $string .= '()';
        }

        $string .= ' ' . ($this->isDynamic ? 'd' : 's');

        if ($this->allowedMethods) {
            $string .= '[' . implode('|', array_keys($this->allowedMethods)) . ']';
        } else {
            $string .= '[*]';
        }

        return $string;
    }

    /**
     * Check if the provided route is the same as this
     * @param mixed $route The route to check
     * @return boolean True if the route is the same, false otherwise
     */
    public function equals($route) {
        if (!$route instanceof self) {
            return false;
        }

        if (((string) $route) != ((string) $this)) {
            return false;
        }

        if ($route->id != $this->id) {
            return false;
        }

        return true;
    }

    /**
     * Sets the URL path
     * @param string $path
     * @return null
     * @throws zibo\ZiboException when the path is empty or invalid
     */
    private function setPath($path) {
        self::validatePath($path);

        $this->path = $path;
    }

    /**
     * Gets the URL path
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Gets the full URL for this route
     * @param string $baseUrl The base URL
     * @param array $arguments Array with the argument name as key and the
     * argument as value. The argument should be a scalar value which will be
     * url encoded
     * @return string The generated URL
     */
    public function getUrl($baseUrl = null, array $arguments = null) {
        if (!$arguments) {
            return $baseUrl . $this->path;
        }

        $path = $baseUrl;

        $tokens = self::tokenizePath($this->path);

        foreach ($tokens as $index => $token) {
            $argumentName = substr($token, 1, -1);
            $isArgument = $token == '%' . $argumentName . '%';

            if ($isArgument && isset($arguments[$argumentName])) {
                if (!is_scalar($arguments[$argumentName])) {
                    throw new RouterException('Argument ' . $argumentName . ' is not a scalar value');
                }

                $path .= '/' . urlencode($arguments[$argumentName]);
            } else {
                $path .= '/' . $token;
            }
        }

        return $path;
    }

    /**
     * Sets the dynamic parameters flag
     * @param boolean $isDynamic
     * @return null
     */
    public function setIsDynamic($isDynamic) {
        $this->isDynamic = $isDynamic;
    }

    /**
     * Gets the dynamic parameters flag
     * @return boolean
     */
    public function isDynamic() {
        return $this->isDynamic;
    }

    /**
     * Sets the callback of this route
     * @param string $callback string|array Callback to the action of the route
     * @return null
     */
    public function setCallback($callback) {
        $this->callback = $callback;
    }

    /**
     * Gets the callback for this route
     * @return string|array
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * Sets the id of this route
     * @param string $id The id of this route
     * @throws zibo\library\router\exception\RouterException
     */
    public function setId($id = null) {
        if ($id !== null && (!is_string($id) || $id == '')) {
            throw new RouterException('Provided id is empty or not a string');
        }

        $this->id = $id;
    }

    /**
     * Gets the id of this route
     * @return string|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the allowed methods for this route
     * @param null|string|array $allowedMethods The allowed methods of this route
     * @return null
     * @throws zibo\library\router\exception\RouterException
     */
    public function setAllowedMethods($allowedMethods = null) {
        if ($allowedMethods === null) {
            $this->allowedMethods = null;
            return;
        }

        if (!is_array($allowedMethods)) {
            $allowedMethods = array($allowedMethods);
        }

        $this->allowedMethods = array();

        foreach ($allowedMethods as $index => $allowedMethod) {
            if (!is_string($allowedMethod)) {
                throw new RouterException('Invalid method provided');
            }

            $this->allowedMethods[strtoupper(trim($allowedMethod))] = true;
        }

        ksort($this->allowedMethods);
    }

    /**
     * Gets the allowed methods of this route
     * @return array|null
     */
    public function getAllowedMethods() {
        return $this->allowedMethods;
    }

    /**
     * Checks if the provided method is allowed
     * @param string $method The request method
     * @return boolean
     */
    public function isMethodAllowed($method) {
        if ($this->allowedMethods === null) {
            return true;
        }

        return isset($this->allowedMethods[strtoupper($method)]);
    }

    /**
     * Sets the arguments for the action method
     * @param array $arguments
     * @return null
     */
    public function setArguments(array $arguments = null) {
        $this->arguments = $arguments;
    }

    /**
     * Gets the arguments for the action
     * @return array Arguments for the action method in the controller
     */
    public function getArguments() {
        if ($this->arguments === null) {
            return array();
        }

        return $this->arguments;
    }

    /**
     * Sets the predefined arguments for the action method
     * @param array $arguments
     * @return null
     */
    public function setPredefinedArguments(array $arguments = null) {
        $this->predefinedArguments = $arguments;
    }

    /**
     * Gets the predefined arguments for the action method
     * @return array Arguments for the action method in the controller
     */
    public function getPredefinedArguments() {
        if ($this->predefinedArguments === null) {
            return array();
        }

        return $this->predefinedArguments;
    }

    /**
     * Sets the locale for this route
     * @param string|null $locale Locale code of the current locale, null for
     * automatic selection
     * @return null
     */
    public function setLocale($locale = null) {
        $this->locale = $locale;
    }

    /**
     * Gets the locale of this route
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Sets the base URL for this route
     * @param string|null $baseUrl URL pointing to the system
     * @return null
     */
    public function setBaseUrl($baseUrl = null) {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Gets the base URL of this route
     * @return string
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * Tokenizes a path
     * @param string $path
     * @return array Array with the tokens of the path
     */
    public static function tokenizePath($path) {
        if ($path === '/') {
            return array();
        }

        return explode('/', ltrim($path, '/'));
    }

    /**
     * Validates a HTTP path
     * @param string $path The path to validate
     * @return null
     * @throws zibo\library\router\exception\RouterException  when the path is
     * empty or invalid
     */
    public static function validatePath($path) {
        if (!is_string($path) || ! $path) {
            throw new RouterException('Provided path is empty or not a string.');
        }

        $regexHttpSegment = '(([a-zA-Z0-9]|[$+_.-]|%|[!*\'(),])|(%[0-9A-Fa-f][0-9A-Fa-f])|[;:@&=])*';
        $regexHttpPath = '/^' . $regexHttpSegment . '(\\/' . $regexHttpSegment . ')*$/';

        if (!preg_match($regexHttpPath, $path)) {
            throw new RouterException($path . ' is not a valid HTTP path');
        }
    }

}