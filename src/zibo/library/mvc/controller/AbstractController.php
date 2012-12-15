<?php

namespace zibo\library\mvc\controller;

use zibo\library\mvc\Request;
use zibo\library\mvc\Response;

/**
 * Abstract implementation of a controller
 */
class AbstractController implements Controller {

    /**
     * The request for this controller
     * @var zibo\library\mvc\Request
     */
    protected $request;

    /**
     * The response for this controller
     * @var zibo\library\mvc\Response
     */
    protected $response;

    /**
     * Sets the request for this controller
     * @param zibo\library\mvc\Request $request The request
     * @return null
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * Sets the response for this controller
     * @param zibo\library\mvc\Response $response The response
     * @return null
     */
    public function setResponse(Response $response) {
        $this->response = $response;
    }

    /**
     * Hook to execute before every action
     * @return boolean True to execute the action, false to skip it
     */
    public function preAction() {
        return true;
    }

    /**
     * Hook to execute after every action
     * @return null
     */
    public function postAction() {

    }

}