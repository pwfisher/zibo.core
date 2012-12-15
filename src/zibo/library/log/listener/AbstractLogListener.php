<?php

namespace zibo\library\log\listener;

use zibo\library\decorator\StorageSizeDecorator;
use zibo\library\log\LogMessage;

use \Exception;

/**
 * Log listener to echo log items to the screen
 */
abstract class AbstractLogListener implements LogListener {

	/**
	 * Default date format
	 * @var string
	 */
	const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Separator between the fields
	 * @var string
	 */
	const FIELD_SEPARATOR = ' - ';

	/**
	 * Date format for the date
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * Decorator for the memory value
	 * @var zibo\library\decorator\Decorator
	 */
	protected $memoryDecorator;

	/**
	 * Array with the level translated in human readable form
	 * @var array
	 */
	protected $levels;

	/**
	 * Maximum level to log
	 * @var integer
	 */
	protected $level;

    /**
     * Construct a new file log listener
     * @param string $fileName Path of the log file
     * @return null
     */
    public function __construct() {
        $this->dateFormat = self::DEFAULT_DATE_FORMAT;
        $this->memoryDecorator = new StorageSizeDecorator();

        $this->levels = array(
            LogMessage::LEVEL_ERROR => 'E',
            LogMessage::LEVEL_WARNING => 'W',
            LogMessage::LEVEL_INFORMATION => 'I',
            LogMessage::LEVEL_DEBUG => 'D',
        );

        $this->level = 0;
    }

    /**
     * Sets the date format used to write the timestamp of the log item
     * @param string $dateFormat
     * @return null
     */
    public function setDateFormat($dateFormat) {
    	if (!is_string($dateFormat) || $dateFormat == '') {
    		throw new Exception('Provided date format is empty');
    	}

    	$this->dateFormat = $dateFormat;
    }

    /**
     * Gets the date format used to write the timestamp of the log item
     * @return string date format
     */
    public function getDateFormat() {
    	return $this->dateFormat;
    }

    /**
     * Sets the log level
     * @param integer $level 0 for all levels, see LogMessage level constants
     * @return null
     * @see LogMessage
     */
    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * Gets the log level
     * @return integer
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * Checks if the provided level should be logged
     * @param integer $level Level to check
     * @return boolean True to log, false otherwise
     */
    protected function isLoggable($level) {
        if (!$this->level || $this->level & $level) {
            return true;
        }

        return false;
    }

    /**
     * Get the output string of a log item
     * @param zibo\library\log\LogMessage $message
     * @return string
     */
    protected function getLogMessageAsString(LogMessage $message) {
        $output = $message->getRequestId();
        $output .= self::FIELD_SEPARATOR . date($this->getDateFormat(), $message->getDate());
        $output .= self::FIELD_SEPARATOR . substr($message->getMicroTime(), 0, 5);
        $output .= self::FIELD_SEPARATOR . $message->getIp();
        $output .= self::FIELD_SEPARATOR . str_pad($message->getSource(), 8);
        $output .= self::FIELD_SEPARATOR . str_pad($this->memoryDecorator->decorate(memory_get_usage()), 9, ' ', STR_PAD_LEFT);
        $output .= self::FIELD_SEPARATOR . $this->levels[$message->getLevel()];
        $output .= self::FIELD_SEPARATOR . $message->getTitle();

        $description = $message->getDescription();
        if (!empty($description)) {
            $output .= self::FIELD_SEPARATOR . $description;
        }
        $output .= "\n";

        return $output;
    }

}