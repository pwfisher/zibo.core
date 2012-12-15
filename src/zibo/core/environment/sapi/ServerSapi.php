<?php

namespace zibo\core\environment\sapi;

use zibo\core\Zibo;

use zibo\library\http\Header;
use zibo\library\http\HeaderContainer;
use zibo\library\http\Request;
use zibo\library\http\Response;
use zibo\library\log\Log;

/**
 * CLI implementation of the server interface with PHP
 */
class ServerSapi implements Sapi {

    /**
     * The name of this sapi
     * @var string
     */
    const NAME = 'zibo';

    /**
     * Response status reason phrases
     * @var array
     */
    private $statusPhrases = array(
        100 => 'Continue', // 100
        101 => 'Switching Protocols', // 101
        Response::STATUS_CODE_OK => 'OK', // 200
        Response::STATUS_CODE_CREATED => 'Created', // 201
        202 => 'Accepted', // 202
        203 => 'Non-Authoritative Information', // 203
        204 => 'No Content', // 204
        205 => 'Reset Content', // 205
        206 => 'Partial Content', // 206
        300 => 'Multiple Choices', // 300
        Response::STATUS_CODE_MOVED_PERMANENTLY => 'Moved Permanently', // 301
        Response::STATUS_CODE_FOUND => 'Found', // 302
        303 => 'See Other', // 303
        Response::STATUS_CODE_NOT_MODIFIED => 'Not Modified', // 304
        305 => 'Use Proxy', // 305
        307 => 'Temporary Redirect', // 307
        Response::STATUS_CODE_BAD_REQUEST => 'Bad Request', // 400
        Response::STATUS_CODE_UNAUTHORIZED => 'Unauthorized', // 401
        402 => 'Payment Required', // 402
        Response::STATUS_CODE_FORBIDDEN => 'Forbidden', // 403
        Response::STATUS_CODE_NOT_FOUND => 'Not Found', // 404
        Response::STATUS_CODE_METHOD_NOT_ALLOWED => 'Method Not Allowed', //405
        Response::STATUS_CODE_SERVER_ERROR => 'Internal Server Error', // 500
        Response::STATUS_CODE_NOT_IMPLEMENTED => 'Not Implemented', //501
    );

    /**
     * Instance of the Log
     * @var zibo\library\log\Log
     */
    protected $logged;

    /**
     * The socket of the current connection
     * @var resource
     */
    protected $socket;

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
     * Sets a Log
     * @param zibo\library\log\Log $log
     * @return null
     */
    public function setLog($log) {
        $this->log = $log;
    }

    /**
     * Sets the socket of current connection
     * @param resource $socket
     * @return null
     */
    public function setSocket($socket) {
        $this->socket = $socket;
    }

    /**
     * Sets the incoming HTTP request from the server
     * @param zibo\library\http\Request $request
     * @return null
     */
    public function setHttpRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * Gets the incoming HTTP request from the server
     * @return zibo\library\http\Request
     */
    public function getHttpRequest() {
        return $this->request;
    }

    /**
     * Sends the provided HTTP response to the server
     * @param zibo\library\http\Response
     */
    public function sendHttpResponse(Response $response) {
        $response->addHeader(Header::HEADER_ACCEPT_RANGES, 'none');
        $response->addHeader('Server', 'Zibo ' . Zibo::VERSION);

        $statusCode = $response->getStatusCode();

        $output = $this->request->getProtocol() . ' ' . $statusCode . ' ' . $this->getHttpResponseStatusPhrase($statusCode);
        $output .= "\r\n";
        $output .= $response->getHeaders();

        $cookies = $response->getCookies();
        foreach ($cookies as $cookie) {
            $output .= Header::HEADER_SET_COOKIE . ': ' . $cookie . "\r\n";
        }

        $output .= "\r\n";

//         $this->log->logInformation('output', $output);

        fwrite($this->socket, $output);
        if ($this->request->getMethod() != Request::METHOD_HEAD) {
            fwrite($this->socket, $response->getBody());
        }
    }

    /**
     * Gets the status phrase for the provided status code
     * @param integer $statusCode HTTP response status code
     * @return string HTTP response status phrase
     */
    public function getHttpResponseStatusPhrase($statusCode) {
        if (!isset($this->statusPhrases[$statusCode])) {
            return 'Unknown Status';
        }

        return $this->statusPhrases[$statusCode];
    }

}