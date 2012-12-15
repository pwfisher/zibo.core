<?php

namespace zibo\core\mvc\message;

use \Exception;

/**
 * Message data container
 */
class Message {

    /**
     * Type for a error message
     * @var string
     */
    const TYPE_ERROR = 'error';

    /**
     * Type for a information message
     * @var string
     */
    const TYPE_INFORMATION = 'info';

    /**
     * Type for a success message
     * @var string
     */
    const TYPE_SUCCESS = 'success';

    /**
     * Type for a warning message
     * @var string
     */
    const TYPE_WARNING = 'warning';

    /**
     * The message
     * @var string
     */
    protected $message;

    /**
     * The type of the message
     * @var string
     */
    protected $type;

    /**
     * Construct a new message
     * @param string $message the message
     * @param string $type type of the message
     * @return null
     */
    public function __construct($message, $type = null) {
        $this->setMessage($message);
        $this->setType($type);
    }

    /**
     * Sets the message
     * @param string $message
     * @return null
     * @throws Exception when the provided message is empty or invalid
     */
    public function setMessage($message) {
        if (!is_string($message) || !$message) {
            throw new Exception('The provided message is invalid or empty');
        }

        $this->message = $message;
    }

    /**
     * Gets the message
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Set the type of this message
     * @param string $type
     * @return null
     * @throws Exception when the provided type is not null or not a string
     */
    public function setType($type = null) {
        if ($type !== null && (!is_string($type) || !$type)) {
            throw new Exception('The provided type is invalid or empty');
        }

        $this->type = $type;
    }

    /**
     * Get the type of this message
     * @return string
     */
    public function getType() {
        return $this->type;
    }

}