<?php

namespace zibo\library\mvc;

use zibo\library\http\Request as HttpRequest;
use zibo\library\http\Response as HttpResponse;
use zibo\library\mvc\view\View;

/**
 * A extension of the HTTP request with view
 */
class Response extends HttpResponse {

    /**
     * The view for this response
     * @var zibo\core\view\View
     */
    protected $view;

    /**
     * Sets the view of this response. A view will override the body when
     * sending the response
     * @param zibo\core\view\View $view The view
     * @return null
     */
    public function setView(View $view = null) {
        $this->view = $view;
    }

    /**
	 * Returns the view of this response.
	 * @return zibo\core\view\View The view
	 */
    public function getView() {
        return $this->view;
    }

    /**
     * Returns the body of this response
     * @return string The body
     */
    public function getBody() {
        if ($this->view) {
            return $this->view->render(true);
        }

        return $this->body;
    }

    /**
     * Sends the response to the client
     * @param zibo\library\http\Request $request The request to respond to
     * @return null
     */
    public function send(HttpRequest $request) {
        $this->sendHeaders($request->getProtocol());

        if ($this->willRedirect()) {
            return;
        }

        if ($this->view) {
            $this->view->render(false);
        } else {
            echo $this->body;
        }
    }

}