<?php

namespace zibo\core\server;

use zibo\core\mvc\view\FileView;
use zibo\core\Mime;
use zibo\core\Zibo;

use zibo\library\filesystem\File;
use zibo\library\http\Header;
use zibo\library\http\Request;
use zibo\library\log\Log;
use zibo\library\mvc\Response;

use \Exception;

/**
 * Worker process for the server
 */
class Worker {

    /**
     * Instance of the server
     * @var Server
     */
    private $server;

    /**
     * Instance of Zibo
     * @var zibo\core\Zibo
     */
    private $zibo;

    /**
     * Log of the server
     * @var zibo\library\log\Log
     */
    private $log;

    /**
     * Request to process
     * @var zibo\library\http\Request
     */
    private $request;

    /**
     * The socket of the client connection
     * @var resource
     */
    private $connection;

    /**
     * Resources which should always be hosted from the public directory
     * @var array
     */
    private $publicResources;

    /**
     * Constructs a new worker
     * @param Server $server Instance of the server
     * @param zibo\library\http\Request $request Incoming request
     * @param resource $connection Client connection handle
     * @return null
     */
    public function __construct(Server $server, Request $request, $connection) {
        $this->server = $server;
        $this->zibo = $server->getZibo();
        $this->log = $server->getLog();
        $this->request = $request;
        $this->connection = $connection;

        $this->publicResources = array(
            'favicon.ico' => true,
            'robots.txt' => true,
        );
    }

    /**
     * Gets the client connection handle
     * @return resource
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Handle the request
     * @return null
     */
    public function work() {
        // incoming request finished, handle it
        try {
            $sapi = $this->zibo->getEnvironment()->getSapi();
            $sapi->setSocket($this->connection);
            $sapi->setHttpRequest($this->request);

            $response = $this->handleRequest();
            if ($response) {
                $sapi->sendHttpResponse($response);
            } else {
                $this->zibo->service();
            }
        } catch (Exception $exception) {
            $this->log->logException($exception);

            fwrite($this->connection, $this->request->getProtocol() . " 500 Internal Server Error\n\n");
        }

        $this->server->closeConnection($this->connection);
        $this->log->logInformation('---------------------', null, Server::LOG_SOURCE);
    }

    /**
     * Try to host the resource directly
     * @param zibo\library\http\Request $request The incoming request
     * @return zibo\library\mvc\Response|boolean A response if the worker
     * handled the request, false otherwise
     */
    protected function handleRequest() {
        $method = $this->request->getMethod();
        if ($method != Request::METHOD_GET && $method != Request::METHOD_HEAD) {
            return false;
        }

        $path = $this->request->getPath();
        if ($path == '/') {
            return false;
        }

        $path = substr($path, 1);
        $file = new File($this->zibo->getPublicDirectory(), $path);

        $isForced = false;
        if (!$file->exists()) {

            if (!isset($this->publicResources[$path])) {
                return false;
            }

            $isForced = true;
        }

        $this->log->logInformation('Receiving request', $method . ' ' . $this->request->getPath(), Server::LOG_SOURCE);

        $headers = $this->request->getHeaders();
        foreach ($headers as $header) {
            $this->log->logInformation('Receiving header', $header, Server::LOG_SOURCE);
        }

        $this->log->logInformation('Serving static file', $file, Server::LOG_SOURCE);

        $response = new Response();
        if ($isForced) {
            $response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);
        } elseif ($file->getExtension() == 'php') {
            $response->setStatusCode(Response::STATUS_CODE_FORBIDDEN);
        } else {
            $view = new FileView($file);
            $view->setPassthruHandle($this->connection);

            $response->setView($view);
            $response->setLastModified($file->getModificationTime());
            $response->addHeader(Header::HEADER_CONTENT_LENGTH, $file->getSize());
            $response->addHeader(Header::HEADER_CONTENT_TYPE, Mime::getMimeType($this->zibo, $file));
        }

        $this->log->logInformation('Sending response', 'Status code ' . $response->getStatusCode(), Server::LOG_SOURCE);
        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            $this->log->logInformation('Sending header', $header, Server::LOG_SOURCE);
        }

        return $response;
    }

}