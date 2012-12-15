<?php

namespace zibo\library\config\io\ini;

use zibo\library\config\exception\ConfigException;
use zibo\library\config\Config;

/**
 * Helper for the IniConfigIO
 */
class IniParser {

    /**
     * Prefix for reserved words
     * @var string
     */
    const RESERVED_PREFIX = 'ZZZ';

    /**
     * Suffix for reserved words
     * @var string
     */
    const RESERVED_SUFFIX = 'ZZZ';

    /**
     * Reserved words of the PHP ini parser
     * @var array
     */
    private $reservedWords = array('null', 'yes', 'no', 'true', 'false', 'on', 'off', 'none',
                                   'NULL', 'YES', 'NO', 'TRUE', 'FALSE', 'ON', 'OFF', 'NONE');
    // reserved keys: null, yes, no, true, false, on, off, none
    // reserved chars: {}|&~![()^"

    /**
     * Variables to replace in provided values
     * @var array
     */
    private $variables;

    /**
     * Sets the variables to use in parseVariables
     * @param array $variables Array with values
     * @return null
     * @see parseVariables()
     */
    public function setVariables(array $variables = null) {
        $this->variables = $variables;
    }

    /**
     * Parses the variables into the provided value
     * @param string $string The value to parse the variables into
     * @param string $varDelimiter The prefix and suffix of a variable name
     * @return string The provided value with the variables parsed into
     */
    private function parseVariables($string, $varDelimiter = '%') {
        if (!is_string($string) || !$string|| !isset($this->variables)) {
            return $string;
        }

        foreach ($this->variables as $variable => $value) {
            $string = str_replace($varDelimiter . $variable . $varDelimiter, $value, $string);
        }

        return $string;
    }

    /**
     * Gets the ini string for the provided configuration
     * @param array|mixed $values Hierarchic array with each configuration token as a key
     * @param string $key The key for the provided values (for recursive calls)
     * @return string Ini of the provided config
     * @throws zibo\library\config\exception\ConfigException when the provided
     * config is not an array and no key is provided
     */
    public function getIniString($values, $key = null) {
        $output = '';

        if (is_array($values)) {
            foreach ($values as $k => $v) {
                $newKey = is_null($key) ? $k : $key . Config::TOKEN_SEPARATOR . $k;
                $output .= $this->getIniString($v, $newKey);
            }
        } elseif (is_null($key)) {
            throw new ConfigException('Provided key is null and the values are not an array. Make sure $values is an array if you leave $key empty.');
        } else {
            if (is_null($values)) {
                return $output;
            } elseif (is_bool($values)) {
                $values = $values === true ? '1' : '0';
            } elseif (!ctype_alnum($values)) {
                $values = addslashes($values);
                $values = '"' . $values . '"';
            }
            $output .= $key . ' = ' . $values . "\n";
        }

        return $output;
    }

    /**
     * Parses the provided ini string and adds the values to the provided
     * configuration array
     * @param array $config Hierarchic array with each configuration token as a key
     * @param string $ini Ini configuration string
     * @return null
     * @throws zibo\library\config\exception\ConfigException when the provided
     * ini could not be parsed
     */
    public function setIniString(&$config, $ini) {
        // parse the ini string into an array
        $parsedIni = @parse_ini_string($ini, true);

        if ($parsedIni === false) {
            // the ini string could not be parsed, let's prefix and suffix the
            // reserved words and try again
            $this->parseReservedWords($ini);

            $parsedIni = @parse_ini_string($ini, true, INI_SCANNER_RAW);
            if ($parsedIni === false) {
                $error = error_get_last();
                throw new ConfigException('Could not parse the provided ini: ' . $error['message']);
            }

            $parsedIni = $this->unparseReservedWordsFromIni($parsedIni);
        }

        $parsedIni = Config::flattenConfig($parsedIni);

        // adds the parsed values to the configuration array
        foreach ($parsedIni as $key => $value) {
            $value = $this->parseVariables($value);
            Config::setValue($config, $key, $value);
        }
    }

    /**
     * Unparse the reserved words from the provided ini
     * @param array $ini
     * @return array
     */
    private function unparseReservedWordsFromIni(array $ini) {
        $unparsedIni = array();

        foreach ($ini as $key => $value) {
            $this->unparseReservedWords($key);

            if (is_array($value)) {
                $value = $this->unparseReservedWordsFromIni($value);
            } else {
                $this->unparseReservedWords($value);
            }

            $unparsedIni[$key] = $value;
        }

        return $unparsedIni;
    }

    /**
     * Adds the prefix and suffix to the reserved words
     * @param string $string
     */
    private function parseReservedWords(&$string) {
        foreach ($this->reservedWords as $reservedWord) {
            $string = str_replace($reservedWord, self::RESERVED_PREFIX . $reservedWord . self::RESERVED_SUFFIX, $string);
        }
    }

    /**
     * Removes the prefix and suffix from the reserved words
     * @param string $string
     */
    private function unparseReservedWords(&$string) {
        foreach ($this->reservedWords as $reservedWord) {
            $string = str_replace(self::RESERVED_PREFIX . $reservedWord . self::RESERVED_SUFFIX, $reservedWord, $string);
        }
    }

}