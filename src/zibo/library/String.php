<?php

namespace zibo\library;

use \Exception;

/**
 * String helper library
 */
class String {

    /**
     * Option to check for string value
     * @var integer
     */
    const STRING = 0;

    /**
     * Option to check for non empty string values
     * @var integer
     */
    const NOT_EMPTY = 1;

    /**
     * Default character haystack for generating strings
     * @var string
     */
    const GENERATE_HAYSTACK = '123456789bcdfghjkmnpqrstvwxyz';

    /**
     * Checks whether a string is empty
     * @param mixed $value Value to check
     * @return boolean True if the provided value is a empty string
     * @throws zibo\ZiboException when the provided value is a object or an array
     */
    public static function isString($value, $options = self::STRING) {
        if ($options & self::NOT_EMPTY && $value != '0' && empty($value)) {
            return false;
        }

        if (!is_numeric($value) && !is_string($value)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the provided string starts with the provided start
     * @param string $string String to check
     * @param string|array $start String to check as start or an array of strings
     * @return boolean True when the provided string starts with the provided start
     */
    public static function startsWith($string, $start) {
        if (!is_array($start)) {
            $start = array($start);
        }

        foreach ($start as $token) {
            $startLength = strlen($token);
            if (strncmp($string, $token, $startLength) == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Truncates the provided string
     * @param string $string String to truncate
     * @param integer $length Number of characters to keep
     * @param string $etc String to truncate with
     * @param boolean $breakWords Set to true to keep words as a whole
     * @return string Truncated string
     * @throws Exception when the provided length is not a positive integer
     */
    public static function truncate($string, $length = 80, $etc = '...', $breakWords = false) {
        if (!$string) {
            return '';
        }

        if (!is_integer($length) || $length <= 0) {
            throw new Exception('Provided length is not a positive integer');
        }

        if (strlen($string) < $length) {
            return $string;
        }

        $length -= strlen($etc);
        if (!$breakWords) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
        }

        return substr($string, 0, $length) . $etc;
    }

    /**
     * Generates a random string
     * @param integer $length Number of characters to generate
     * @param string $haystack String with the haystack to pick characters from
     * @return string A random string
     * @throws zibo\ZiboException when an invalid length is provided
     * @throws zibo\ZiboException when an empty haystack is provided
     * @throws zibo\ZiboException when the requested length is greater then
     * the length of the haystack
     */
    public static function generate($length = 8, $haystack = null) {
        $string = '';
        if ($haystack === null) {
            $haystack = self::GENERATE_HAYSTACK;
        }

        if (!is_integer($length) || $length <= 0) {
            throw new Exception('Could not generate a random string: invalid length provided');
        }

        if (!is_string($haystack) || !$haystack) {
            throw new Exception('Could not generate a random string: empty or invalid haystack provided');
        }

        $haystackLength = strlen($haystack);
        if ($length > $haystackLength) {
            throw new Exception('Length cannot be greater than the length of the haystack. Length is ' . $length . ' and the length of the haystack is ' . $haystackLength);
        }

        $i = 0;
        while ($i < $length) {
            $index = mt_rand(0, $haystackLength - 1);

            $string .= $haystack[$index];

            $i++;
        }

        return $string;
    }

    /**
     * Gets a safe string for file name and URL usage
     * @param string $string String to make safe
     * @param string $replacement Replacement string for all non alpha numeric characters
     * @param boolean $lower Set to false to skip strtolower
     * @return string Safe string for file names and URLs
     */
    public static function safeString($string, $replacement = '-', $lower = true) {
    	if ((!is_string($string) && !is_numeric($string)) || $string == '') {
    		throw new Exception('Provided string is invalid or empty');
    	}

        $encoding = mb_detect_encoding($string);
        if ($encoding != 'ASCII') {
            $string = iconv($encoding, 'ASCII//TRANSLIT//IGNORE', $string);
        }

        $string = preg_replace("/[\s]/", $replacement, $string);
        $string = preg_replace("/[^A-Za-z0-9._-]/", '', $string);

        if ($lower) {
            $string = strtolower($string);
        }

        return $string;
    }

    /**
     * Adds line numbers to the provided string
     * @param string $string String to add line numbers to
     * @return string String with line numbers added
     */
    public static function addLineNumbers($string) {
        $output = '';
        $lineNumber = 1;
        $lines = explode("\n", $string);
        $lineMaxDigits = strlen(count($lines));

        foreach ($lines as $line) {
            $output .= str_pad($lineNumber , $lineMaxDigits, '0', STR_PAD_LEFT) . ': ' . $line . "\n";
            $lineNumber++;
        }

        return $output;
    }

}