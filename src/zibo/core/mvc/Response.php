<?php

namespace zibo\core\mvc;

use zibo\core\mvc\message\Message;
use zibo\core\mvc\message\MessageContainer;

use zibo\library\mvc\Response as MvcResponse;

/**
 * A extension of the MVC request with messages
 */
class Response extends MvcResponse {

    /**
	 * Container for notification messages
	 * @var zibo\core\mvc\message\MessageContainer
     */
    protected $messageContainer;

    /**
     * Constructs a new response
     * @return null
     */
    public function __construct() {
        parent::__construct();

        $this->messageContainer = new MessageContainer();
    }

    /**
     * Add a message to the response
     * @param zibo\core\mvc\message\Message $message The message to add
     * @return null
     */
    public function addMessage(Message $message) {
        $this->messageContainer->add($message);
    }

    /**
     * Checks if there are messages added to the response
     * @return boolean
     */
    public function hasMessages() {
        return $this->messageContainer->hasMessages();
    }

    /**
     * Gets the message container
     * @return zibo\core\mvc\message\MessageContainer
     */
    public function getMessageContainer() {
        return $this->messageContainer;
    }

}