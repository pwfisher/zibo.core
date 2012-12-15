<?php

namespace zibo\library\log;

use \Exception;

/**
 * Data container of a log item
 */
class LogMessage {

    /**
     * Error level
     * @var integer
     */
    const LEVEL_ERROR = 1;

    /**
     * Warning level
     * @var integer
     */
    const LEVEL_WARNING = 2;

    /**
     * Information level
     * @var integer
     */
    const LEVEL_INFORMATION = 4;

    /**
     * Debug level
     * @var integer
     */
    const LEVEL_DEBUG = 8;

    /**
     * The id of the request
     * @var string
     */
    private $id;

    /**
     * The title
     * @var mixed
     */
    private $title;

    /**
     * The description
     * @var string
     */
    private $description;

    /**
     * The level of this message
     * @var integer
     */
    private $level;

    /**
     * The source of this item
     * @var string
     */
    private $source;

    /**
     * The timestamp
     * @var integer
     */
    private $date;

    /**
     * The microtime in the request
     * @var integer
     */
    private $microtime;

    /**
     * The IP address of the client
     * @var string
     */
    private $ip;

    /**
     * Constructs a new log message
     * @param integer $level
     * @param mixed $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function __construct($level, $title, $description = null, $source = null) {
        $this->setLevel($level);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setSource($source);

        $this->requestId = null;
        $this->date = time();
        $this->microtime = null;
        $this->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['argv'][0];
    }

    /**
     * Sets the id of this request
     * @param mixed $id
     * @return null
     * @throws Exception when the provided id is not a string
     */
    public function setRequestId($id) {
    	if (!is_string($id)) {
            throw new Exception('Invalid id provided');
        }

        $this->id = $id;
    }

    /**
     * Gets the id of the request
     * @return string
     */
    public function getRequestId() {
        return $this->id;
    }

    /**
     * Sets the type of this log item
     * @param integer $type
     * @return null
     * @throws Exception when an invalid type has been provided
     */
    public function setLevel($level) {
        if (is_null($level) || (
                $level != self::LEVEL_ERROR &&
                $level != self::LEVEL_WARNING &&
                $level != self::LEVEL_INFORMATION &&
                $level != self::LEVEL_DEBUG
            )
        ) {
        	$message = 'Provided type is invalid. Try ' . self::LEVEL_ERROR . ' for a error, ' . self::LEVEL_WARNING . ' for a warning, ' . self::LEVEL_INFORMATION . ' for a information message and ' . self::LEVEL_DEBUG . ' for a debug message.';
            throw new Exception($message);
        }

        $this->level = $level;
    }

    /**
     * Gets the level of this log item
     * @return integer
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * Gets whether this item is a error item
     * @return boolean
     */
    public function isError() {
        return $this->level == self::LEVEL_ERROR;
    }

    /**
     * Gets whether this item is a warning item
     * @return boolean
     */
    public function isWarning() {
        return $this->level == self::LEVEL_WARNING;
    }

    /**
     * Gets whether this item is a information item
     * @return boolean
     */
    public function isInformation() {
        return $this->level == self::LEVEL_INFORMATION;
    }

    /**
     * Gets whether this item is a debug item
     * @return boolean
     */
    public function isDebug() {
        return $this->level == self::LEVEL_DEBUG;
    }

    /**
     * Sets the title of this log item
     * @param mixed $title
     * @return null
     * @throws Exception when the provided title is not castable to string
     */
    public function setTitle($title) {
    	if (!is_scalar($title) && !is_object($title) && !method_exists($title, '__toString')) {
            throw new Exception('Invalid title provided: ' . gettype($title));
        }

        $this->title = (string) $title;
    }

    /**
     * Gets the title of this log item
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Sets the description of this log item
     * @param string $description
     * @return null
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Gets the description of this log item
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Sets the source of this log item
     * @param string $source
     * @return null
     */
    public function setSource($source) {
        $this->source = $source;
    }

    /**
     * Gets the source of this log item
     * @return string
     */
    public function getSource() {
        return $this->source;
    }

    /**
     * Gets the timestamp of this log item
     * @return integer
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Sets the micro time in the request
     * @param double $microtime
     * @return null
     */
    public function setMicrotime($microtime) {
        $this->microtime = $microtime;
    }

    /**
     * Gets the microtime of this log in the request
     * @return double
     */
    public function getMicrotime() {
        return $this->microtime;
    }

    /**
     * Gets the IP address of the client
     * @return string
     */
    public function getIp() {
        return $this->ip;
    }

}