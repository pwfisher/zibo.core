<?php

namespace zibo\library\http\client;

use zibo\library\http\Request;

/**
 * Interface for a HTTP client
 */
interface Client {

    /**
     * Performs a DELETE request to the provided URL
     * @param string $url URL of the request
     * @param string|array $body Body variables as a url encoded string or
     * an array with key value pairs
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function delete($url, $body = null, array $headers = null);

    /**
     * Performs a HEAD request to the provided URL
     * @param string $url URL of the request
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function head($url, array $headers = null);

    /**
     * Performs a GET request to the provided URL
     * @param string $url URL of the request
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function get($url, array $headers = null);

    /**
     * Performs a POST request to the provided URL
     * @param string $url URL of the request
     * @param string|array $body Body variables as a url encoded string or
     * an array with key value pairs
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function post($url, $body = null, array $headers = null);

    /**
     * Performs a PUT request to the provided URL
     * @param string $url URL of the request
     * @param string|array $body Body variables as a url encoded string or
     * an array with key value pairs
     * @param array $headers Array with the headers of the request
     * @return zibo\library\http\Response
     */
    public function put($url, $body = null, array $headers = null);

    /**
     * Sets the authentication method
     * @param string $method Authentication method eg. Basic, Digest ...
     * @return null
     */
    public function setAuthenticationMethod($method);

    /**
     * Gets the authentication method
     * @return string
     */
    public function getAuthenticationMethod();

    /**
     * Performs a request
     * @param zibo\library\http\Request $request The request to send
     * @return zibo\library\http\Response The reponse of the request
     */
    public function sendRequest(Request $request);

}