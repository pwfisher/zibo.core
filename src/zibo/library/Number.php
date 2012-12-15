<?php

namespace zibo\library;

/**
 * Number helper library
 */
class Number {

    /**
     * No options, just numeric or not
     * @var integer
     */
    const NUMERIC = 0;

    /**
     * Option for no negative value
     * @var integer
     */
    const NOT_NEGATIVE = 1;

    /**
     * Option for no zero values
     * @var integer
     */
    const NOT_ZERO = 2;

    /**
     * Option for no float values
     * @var integer
     */
    const NOT_FLOAT = 4;

    /**
     * Option for octal values
     * @var integer
     */
    const OCTAL = 8;

    /**
     * Checks for a numeric value
     * @param mixed $value The value to check
     * @param integer $options Binary options for this method, see class constants
     * @return boolean True if the value is numeric and matches the provided 
     * options, false otherwise
     */
    public static function isNumeric($value, $options = self::NUMERIC) {
        // check if the value is numeric
        if (!is_numeric($value)) {
            return false;
        }

        if ($options & self::NOT_NEGATIVE && $value < 0) {
            return false;
        }

        if ($options & self::NOT_ZERO && $value === 0) {
            return false;
        }

        if ($options & self::NOT_FLOAT && $value != round($value)) {
            return false;
        }

        if ($options & self::OCTAL && $value != decoct(octdec($value))) {
            return false;
        }

        return true;
    }

}