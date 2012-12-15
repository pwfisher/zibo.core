<?php

namespace zibo\core\environment\sapi;

use zibo\library\http\Response;

/**
 * Implementation of the server interface with PHP
 */
interface Sapi {

    /**
     * Gets the name of this api
     * @return string
     */
    public function getName();

    /**
     * Gets the incoming HTTP request
     * @return zibo\library\http\Request
     */
    public function getHttpRequest();

    /**
     * Sends the provided HTTP response
     * @param zibo\library\http\Response $response
     * @return null
     */
    public function sendHttpResponse(Response $response);

}