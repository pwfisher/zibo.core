<?php

namespace zibo\core\environment\sapi;

use zibo\library\http\Request;
use zibo\library\http\Response;

/**
 * Generic webserver implementation of the server interface with PHP
 */
class WebSapi implements Sapi {

    /**
     * The name of this sapi
     * @var string
     */
    const NAME = 'web';

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
     * Gets the incoming HTTP request
     * @return zibo\library\http\Request
     */
    public function getHttpRequest() {
        if ($this->request) {
            return $this->request;
        }

        return $this->request = Request::createFromServer();
    }

    /**
     * Sends the provided HTTP response
     * @param zibo\library\http\Response
     */
    public function sendHttpResponse(Response $response) {
        $response->send($this->getHttpRequest());
    }

}