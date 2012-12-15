<?php

namespace zibo\library\log\listener;

use zibo\library\log\LogMessage;

use \Exception;

/**
 * Log listener to Write log items to file
 */
class FileLogListener extends AbstractLogListener {

	/**
	 * Default maximum file size in kb
	 * @var integer
	 */
	const DEFAULT_TRUNCATE_SIZE = 1024;

	/**
	 * File name of the log
	 * @var string
	 */
    private $fileName;

    /**
     * Maximum file size
     * @var integer
     */
    private $fileTruncateSize;

    /**
     * Construct a new file log listener
     * @param string $fileName Path of the log file
     * @return null
     */
    public function __construct($fileName) {
        parent::__construct();

    	if (!is_string($fileName) || $fileName == '') {
    		throw new Exception('Provided file name is empty');
    	}

        $this->fileName = $fileName;
        $this->fileTruncateSize = self::DEFAULT_TRUNCATE_SIZE;
    }

    /**
     * Set the limit in kb before the log file gets truncate
     * @param integer size limit in kilobytes
     * @return null
     */
    public function setFileTruncateSize($size) {
        if (!is_numeric($size) || $size < 0) {
    		throw new Exception($size . ' should be positive or zero');
    	}

    	$this->fileTruncateSize = $size;
    }

    /**
     * Get the limit in kb before the log file gets truncate
     * @oaram integer size limit in kilobytes
     */
    public function getFileTruncateSize() {
    	return $this->fileTruncateSize;
    }

    /**
     * Logs a message to the log file
     * @param zibo\library\log\LogMessage $message
     * @return null
     */
    public function logMessage(LogMessage $message) {
        if (!$this->isLoggable($message->getLevel())) {
            return;
        }

        $output = $this->getLogMessageAsString($message);

        if ($this->writeFile($output)) {
            $this->truncateFile($output);
        }
    }

    /**
     * Append the output to the log file
     * @param string output to append
     */
    private function writeFile($output) {
        if (!($f = @fopen($this->fileName, 'a'))) {
            return false;
        }

        fwrite($f, $output);
        fclose($f);

        return true;
    }

    /**
     * Truncate the log tile if the truncate size is set and the log file is bigger then the truncate size
     * @param string output string to write in the truncated file, empty by default
     */
    private function truncateFile($output = '') {
    	$truncateSize = $this->getFileTruncateSize();
    	if (!$truncateSize) {
    		return;
    	}

    	clearstatcache();

        $fileSize = filesize($this->fileName) / 1024; // we work with kb
        if ($fileSize < $truncateSize) {
        	return;
        }

        if ($f = @fopen($this->fileName, 'w')) {
            fwrite($f, $output);
            fclose($f);
        }
    }

}