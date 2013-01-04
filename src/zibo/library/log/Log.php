<?php

namespace zibo\library\log;

use zibo\library\log\listener\LogListener;
use zibo\library\Timer;
use zibo\library\String;

use \Exception;

/**
 * The log interface
 */
class Log {

    /**
     * The request id of this Log
     * @var string
     */
    private $requestId;

    /**
     * The timer of this Log
     * @var zibo\library\Timer
     */
    private $timer;

    /**
     * The log listeners
     * @var array
     */
    private $listeners;

    /**
     * Constructs a new Log
     * @return null
     */
    public function __construct() {
        $this->requestId = substr(md5(time() . '-' . String::generate(8)), 0, 10);
        $this->timer = new Timer();
        $this->listeners = array();
    }

    /**
     * Gets the request id
     * @return string
     */
    public function getRequestId() {
        return $this->requestId;
    }

    /**
     * Adds a listener
     * @param zibo\library\log\listener\LogListener $listener
     * @return null
     */
    public function addLogListener(LogListener $listener) {
        $this->listeners[] = $listener;
    }

    /**
     * Adds a error message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logError($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_ERROR, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Adds a exception
     * @param Exception $exception
     * @param string $source
     * @return null
     */
    public function logException(Exception $exception, $source = null) {
        $stack = array();

        do {
            $message = $exception->getMessage();

            $title = get_class($exception) . (!empty($message) ? ': ' . $message : '');
            $description = $exception->getTraceAsString();

            $stack[] = new LogMessage(LogMessage::LEVEL_ERROR, $title, $description, $source);

            $exception = $exception->getPrevious();
        } while ($exception);

        array_reverse($stack);

        foreach ($stack as $message) {
            $this->logMessage($message);
        }
    }

    /**
     * Adds a warning message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logWarning($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_WARNING, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Adds a information message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logInformation($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_INFORMATION, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Adds a debug message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logDebug($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_DEBUG, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Logs a message to the listeners
     * @param LogMessage $message
     * @return null
     */
    public function logMessage(LogMessage $message) {
        $message->setRequestId($this->requestId);
        $message->setMicrotime($this->getTime());

        foreach ($this->listeners as $listener) {
            $listener->logMessage($message);
        }
    }

    /**
     * Gets the microtime of this Log
     * @return float
     */
    public function getTime() {
        return $this->timer->getTime();
    }

}