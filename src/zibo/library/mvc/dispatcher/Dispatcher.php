<?php

namespace zibo\library\mvc\dispatcher;

use zibo\library\mvc\Request;
use zibo\library\mvc\Response;

/**
 * Interface for a dispatcher of request objects
 */
interface Dispatcher {

    /**
     * Dispatches a request to the action of the controller
     * @param zibo\library\mvc\Request $request The request to dispatch
     * @param zibo\library\mvc\Response $response The response to dispatch the
     * request to
     * @return mixed The return value of the action
     */
    public function dispatch(Request $request, Response $response);

}