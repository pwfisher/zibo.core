<?php

namespace zibo\library\http\client;

use zibo\core\Zibo;

use zibo\library\http\exception\HttpException;
use zibo\library\http\Header;
use zibo\library\http\HeaderContainer;
use zibo\library\http\Request;
use zibo\library\log\Log;

/**
 * Abstract implementation of the HTTP client
 */
abstract class AbstractClient implements Client {

    /**
     * Source for the log messages
     * @var string
     */
    const LOG_SOURCE = 'http';

    /**
     * Instance of the log
     * @var zibo\library\log\Log
     */
    protected $log;

    /**
     * Username for the last created request
     * @var string
     */
    protected $username;

    /**
     * Password for the last created request
     * @var string
     */
    protected $password;

    /**
     * Sets the log
     * @param zibo\library\log\Log $log
     * @return null
     */
    public function setLog(Log $log) {
        $this->log = $log;
    }

    /**
     * Performs a DELETE request to the provided URL
     * @param string $url URL of the request
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function delete($url, array $headers = null) {
        $headers = $this->createHeaderContainer($headers);
        $request = $this->createRequest(Request::METHOD_DELETE, $url, $headers);

        return $this->sendRequest($request);
    }

    /**
     * Performs a HEAD request to the provided URL
     * @param string $url URL of the request
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function head($url, array $headers = null) {
        $headers = $this->createHeaderContainer($headers);
        $request = $this->createRequest(Request::METHOD_HEAD, $url, $headers);

        return $this->sendRequest($request);
    }

    /**
     * Performs a GET request to the provided URL
     * @param string $url URL of the request
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function get($url, array $headers = null) {
        $headers = $this->createHeaderContainer($headers);
        $request = $this->createRequest(Request::METHOD_GET, $url, $headers);

        return $this->sendRequest($request);
    }

    /**
     * Performs a POST request to the provided URL
     * @param string $url URL of the request
     * @param string|array $post Body variables as a url encoded string or
     * an array with key value pairs
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function post($url, $body, array $headers = null) {
        $headers = $this->createHeaderContainer($headers);
        $request = $this->createRequest(Request::METHOD_POST, $url, $headers, $body);

        return $this->sendRequest($request);
    }

    /**
     * Performs a PUT request to the provided URL
     * @param string $url URL of the request
     * @param string|array $body Body variables as a url encoded string or
     * an array with key value pairs
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function put($url, $body, array $headers = null) {
        $headers = $this->createHeaderContainer($headers);
        $request = $this->createRequest(Request::METHOD_PUT, $url, $headers, $body);

        return $this->sendRequest($request);
    }

    /**
     * Creates a header container from the provided headers
     * @param array $headers Header key-value pair
     * @return zibo\library\http\HeaderContainer
     */
    protected function createHeaderContainer(array $headers = null) {
        $container = new HeaderContainer();

        if (!$headers) {
            return $container;
        }

        foreach ($headers as $header => $value) {
            $container->addHeader($header, $value);
        }

        if (!$container->hasHeader(Header::HEADER_USER_AGENT)) {
            $container->addHeader(Header::HEADER_USER_AGENT, 'Zibo ' . Zibo::VERSION);
        }

        return $container;
    }

    /**
     * Creates a HTTP request
     * @param string $method HTTP method (GET, POST, ...)
     * @param string $url URL for the request
     * @param zibo\library\http\HeaderContainer $headers Headers for the
     * request
     * @param string|array $body URL encoded string or an array of request
     * body arguments
     * @return zibo\library\http\Request
     */
    protected function createRequest($method, $url, HeaderContainer $headers, $body = null) {
        $vars = parse_url($url);

        if (isset($vars['path'])) {
            $path = $vars['path'];
        } else {
            $path = '/';
        }

        if (isset($vars['user'])) {
            $this->username = $vars['user'];
        }

        if (isset($vars['pass'])) {
            $this->password = $vars['pass'];
        }

        if (isset($vars['host'])) {
            $headers->setHeader(Header::HEADER_HOST, $vars['host'], true);
        }

        if (isset($vars['query'])) {
            $path .= '?' . $vars['query'];
        }

        $request = new Request($path, $method, 'HTTP/1.1', $headers, $body);

        if (isset($vars['scheme']) && $vars['scheme'] == 'https') {
            $request->setIsSecure(true);
        }

        return $request;
    }

}