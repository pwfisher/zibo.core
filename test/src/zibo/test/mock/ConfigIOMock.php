<?php

namespace zibo\test\mock;

use zibo\library\config\io\ConfigIO;

class ConfigIOMock implements ConfigIO {

	private $hasReadAll = false;
	private $read = array();
	private $written = array();

    private $values = array();

    public function setValues($key, $values) {
        $this->values[$key] = $values;
    }

    public function readAll() {
    	$this->hasReadAll = true;
    	return $this->values;
    }

    public function read($key) {
    	$this->read[$key] = $key;

        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        return array();
    }

    public function write($key, $value) {
        $this->written[$key] = $value;
    }

    public function hasReadAll() {
        return $this->hasReadAll;
    }

    public function hasRead($key) {
    	return isset($this->read[$key]);
    }

    public function hasWritten($key) {
    	return isset($this->written[$key]);
    }

    public function getReadValues() {
        return $this->read;
    }

    public function getWrittenValues() {
        return $this->written;
    }

    public function getAllSections() {
        return array_keys($this->values);
    }

}