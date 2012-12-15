<?php

namespace zibo\core\environment\sapi;

use zibo\core\console\ArgumentParser;

use zibo\library\http\Header;
use zibo\library\http\HeaderContainer;
use zibo\library\http\Request;
use zibo\library\http\Response;

/**
 * CLI implementation of the server interface with PHP
 */
class CliSapi implements Sapi {

    /**
     * The name of this sapi
     * @var string
     */
    const NAME = 'cli';

    /**
     * The request
     * @var zibo\library\http\Request
     */
    protected $request;

    /**
     * Gets the name of this api
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
     * Gets the incoming HTTP request based on the command line arguments
     *
     * The first numeric argument is the request path including query string.
     * The second numeric argument is the request body. The third numeric argument
     * is the request method (GET, POST, ...). The fourth numeric argument is the
     * HTTP version (HTTP/1.0). All other numeric arguments are ignored. The
     * remaining alphanumeric arguments are considered headers. When no method
     * is provided, the request is seen as a GET unless a body is provided,
     * then it will be seen as a POST.
     *
     * @return zibo\library\http\Request
     */
    public function getHttpRequest() {
        if ($this->request) {
            return $this->request;
        }

        $arguments = $_SERVER['argv'];
        array_shift($arguments);

        $arguments = ArgumentParser::parseArguments($arguments);

        if (isset($arguments[0])) {
            $path = $arguments[0];
        } else {
            $path = '/';
        }

        if (isset($argument[1])) {
            $body = $arguments[1];
        } else {
            $body = null;
        }

        if (isset($arguments[2])) {
            $method = $arguments[2];
        } elseif ($body) {
            $method = Request::METHOD_POST;
        } else {
            $method = Request::METHOD_GET;
        }

        if (isset($arguments[3])) {
            $protocol = $arguments[3];
        } else {
            $protocol = 'HTTP/1.1';
        }

        $headers = new HeaderContainer();
        foreach ($arguments as $key => $value) {
            if (is_numeric($key)) {
                continue;
            }

            $headers->addHeader($key, $value);
        }

        // make sure the host is set
        if (!$headers->hasHeader(Header::HEADER_HOST)) {
            $headers->addHeader(Header::HEADER_HOST, 'localhost', true);
        }

        return $this->request = new Request($path, $method, $protocol, $headers, $body);
    }

    /**
     * Sends the provided HTTP response
     * @param zibo\library\http\Response
     */
    public function sendHttpResponse(Response $response) {
        echo $response->getBody();
    }

}