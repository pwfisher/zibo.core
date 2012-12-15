<?php

namespace zibo\library\http\session;

use zibo\library\http\session\io\SessionIO;

use \Exception;

/**
 * Session handler
 */
class Session {

    /**
     * The session io
     * @var zibo\library\http\session\io\SessionIO
     */
    protected $io;

    /**
     * The id of the session
     * @var string
     */
    protected $id;

    /**
     * The data of the session
     * @var array
     */
    protected $data;

    /**
     * Constructs a new session handler
     * @param zibo\library\http\session\io\SessionIO $io Input/Output handler
     * for the session data
     * @return null
     */
    public function __construct(SessionIO $io) {
        $this->io = $io;
        $this->id = null;
        $this->data = array();
    }

    /**
     * Gets the id of the session
     * @return string Id of the session
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Loads a previous session by it's id
     * @param string $id Id of a previous session
     * @return null
     */
    public function read($id) {
        $this->id = $id;
        $this->data = $this->io->read($this->id);
    }

    /**
     * Writes the session to the storage
     * @return null
     */
    public function write() {
        $this->io->write($this->id, $this->data);
    }

    /**
     * Get a value from the session
     * @param string $key key of the value
     * @param mixed $default default value for when the key is not set
     * @return mixed the stored session value, if it does not exist you will get
     * the provided default value
     */
    public function get($key, $default = null) {
        if (!isset($this->data[$key])) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * Gets all the session variables
     * @return array
     */
    public function getAll() {
        return $this->data;
    }

    /**
     * Sets a value to the session or clear a previously set key by passing a
     * null value
     * @param string $key Key of the value
     * @param mixed $value The value, null to clear
     * @return null
     */
    public function set($key, $value = null) {
        if ($value !== null) {
            $this->data[$key] = $value;
        } elseif (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * Clears all values in the session
     * @return null
     */
    public function reset() {
        $this->data = array();
    }

}