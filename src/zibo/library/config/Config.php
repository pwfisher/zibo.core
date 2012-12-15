<?php

namespace zibo\library\config;

use zibo\library\config\exception\ConfigException;
use zibo\library\config\io\ConfigIO;

/**
 * Configuration data container
 *
 * The configuration is defined by key-value pairs. The key is a . separated
 * string. The first token of the key is called the section.
 *
 * eg.
 * database.connection.test = mysql://localhost/test
 */
class Config {

    /**
     * Separator between the tokens of the configuration key
     * @var string
     */
    const TOKEN_SEPARATOR = '.';

    /**
     * Array with the configuration
     * @var array
     */
    private $data;

    /**
     * Configuration input/output implementation
     * @var zibo\library\config\io\ConfigIO
     */
    private $io;

    /**
     * Constructs a new configuration container
     * @param zibo\library\config\io\ConfigIO $io Configuration input/output
     * implementation
     * @return null
     */
    public function __construct(ConfigIO $io) {
        $this->data = array();
        $this->io = $io;
    }

    /**
     * Gets the complete configuration as a tree
     * @return array Tree like array with each configuration key token as a
     * array key
     */
    public function getAll() {
        return $this->data = $this->io->getAll();
    }

    /**
     * Gets a configuration value
     * @param string $key Configuration key
     * @param mixed $default Default value for when the configuration key is
     * not set
     * @return mixed The configuration value if set, the provided default
     * value otherwise
     * @throws zibo\library\config\exception\ConfigException when the key is empty
     * or not a string
     */
    public function get($key, $default = null) {
        $tokens = $this->getKeyTokens($key);

        if (count($tokens) === 1) {
            if (empty($this->data[$key])) {
                return $default;
            }

            return $this->data[$key];
        }

        $result = &$this->data;
        foreach ($tokens as $token) {
            if (!isset($result[$token])) {
                return $default;
            }

            $result = &$result[$token];
        }

        return $result;
    }

    /**
     * Sets a configuration value
     * @param string $key Configuration key
     * @param mixed $value Value for the configuration key
     * @return null
     * @throws zibo\library\config\exception\ConfigException when the key is
     * empty or not a string
     */
    public function set($key, $value) {
        $tokens = $this->getKeyTokens($key);

        self::setValue($this->data, $key, $value);

        $this->io->set($key, $value);
    }

    /**
     * Gets the tokens of a configuration key. This method will read the
     * configuration for the section token (first token) if it has not been read before.
     * @param string $key The configuration key
     * @return array Array with the tokens of the configuration key
     */
    private function getKeyTokens($key) {
        if (!is_string($key) || !$key) {
            throw new ConfigException('Provided key is empty');
        }

        $tokens = explode(self::TOKEN_SEPARATOR, $key);

        $section = $tokens[0];
        if (!isset($this->data[$section])) {
            $this->data[$section] = $this->io->get($section);
        }

        return $tokens;
    }

    /**
     * Sets a value to a hieraric array
     * @param array $config Hierarchic array with configuration values
     * @param string $key The configuration key to add
     * @param mixed $value The value to add
     * @return null
     */
    public static function setValue(array &$config, $key, $value) {
        if (!is_string($key) || !$key) {
            throw new ConfigException('Provided key is empty');
        }

        $data = &$config;

        $tokens = explode(Config::TOKEN_SEPARATOR, $key);
        $numTokens = count($tokens);
        for ($index = 0; $index < $numTokens; $index++) {
            $token = $tokens[$index];
            if ($index == $numTokens - 1) {
                $dataKey = $token;
                break;
            }

            if (isset($data[$token]) && is_array($data[$token])) {
                $data = &$data[$token];
            } else {
                $data[$token] = array();
                $data = &$data[$token];
            }
        }

        $data[$dataKey] = $value;
    }

    /**
     * Parses a hierarchic array into a flat array
     * @param array $config Hierarchic array with configuration values
     * @param string $prefix Prefix for the keys of the configuration array
     * (needed for recursive calls)
     * @return array Flat array of the provided configuration
     */
    public static function flattenConfig(array $config, $prefix = null) {
        $result = array();

        if ($prefix) {
            $prefix .= self::TOKEN_SEPARATOR;
        }

        foreach ($config as $key => $value) {
            $prefixedKey = $prefix . $key;

            if (is_array($value)) {
                $result = self::flattenConfig($value, $prefixedKey) + $result;
            } else {
                $result[$prefixedKey] = $value;
            }
        }

        return $result;
    }

}