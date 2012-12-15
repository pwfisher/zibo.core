<?php

namespace zibo\library\http\client;

use zibo\library\http\Request;

/**
 * Interface for a HTTP client
 */
interface Client {

    /**
     * Performs a request
     * @param zibo\library\http\Request $request The request to send
     * @return zibo\library\http\Response The reponse of the request
     */
    public function sendRequest(Request $request);

}