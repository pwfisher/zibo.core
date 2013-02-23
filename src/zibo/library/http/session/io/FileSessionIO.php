<?php

namespace zibo\library\http\session\io;

use zibo\library\http\exception\HttpException;

use zibo\library\filesystem\File;

/**
 * File implementation for the session input/output
 */
class FileSessionIO implements SessionIO {

    /**
     * The path to save the sessions to
     * @var zibo\library\filesystem\File
     */
    protected $path;

    /**
     * The timeout of the for the sessions in seconds
     * @var integer
     */
    protected $timeout;

    /**
     * Constructs a new file session IO
     * @param zibo\library\filesystem\File $path The path for the session data
     * @return null
     */
    public function __construct(File $path, $timeout) {
        $this->path = $path;
        $this->setTimeout($timeout);
    }

    /**
     * Sets the timeout of the sessions
     * @param integer $timeout Timeout in seconds
     * @return null
     * @throws HttpException When a invalid timeout has been provided
     */
    public function setTimeout($timeout) {
        if (!is_numeric($timeout) || $timeout < 0) {
            throw new HttpException('Provided timeout is not zero or a positive integer');
        }

        $this->timeout = $timeout;
    }

    /**
     * Cleans up the sessions which are invalidated
     * @param boolean $force Set to true to clear all sessions
     * @return null
     */
    public function clean($force = false) {
        $expires = time() - $this->timeout;

        $directory = new File($this->path);
        $sessions = $directory->read();
        foreach ($sessions as $session) {
            if (!$force && $session->getModificationTime() > $expires) {
                continue;
            }

            $session->delete();
        }
    }

    /**
     * Reads the session data for the provided id
     * @param string $id Id of the session
     * @return array Array with the session data
     */
    public function read($id) {
        $file = new File($this->path, $id);
        if (!$file->exists()) {
            return array();
        }

        if ($file->getModificationTime() < (time() - $this->timeout)) {
            $file->delete();

            return array();
        }

        $serialized = $file->read();

        return unserialize($serialized);
    }

    /**
     * Writes the session data to storage
     * @param string $id Id of the session
     * @param array $data Session data
     * @return null
     */
    public function write($id, array $data) {
        $serialized = serialize($data);

        $file = new File($this->path, $id);

        $parent = $file->getParent();
        $parent->create();

        $file->write($serialized);
    }

}