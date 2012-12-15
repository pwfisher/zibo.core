<?php

namespace zibo\library\config\io\ini;

use zibo\library\config\exception\ConfigException;
use zibo\library\config\io\ConfigIO;
use zibo\library\config\Config;
use zibo\library\filesystem\File;

/**
 * Input/output of the configuration in the INI format
 */
class IniConfigIO implements ConfigIO {

    /**
     * Extension for the files which are handled by this I/O implementation
     * @var string
     */
    const FILE_EXTENSION = 'ini';

    /**
     * Helper for the INI format
     * @var zibo\library\config\io\ini\IniParser
     */
    protected $parser;

    /**
     * The paths to read the configuration from
     * @var array
     */
    protected $paths;

    /**
     * The path to write new configuration values to
     * @var zibo\library\filesystem\File
     */
    protected $writePath;

    /**
     * Construct this configuration ini I/O implementation
     * @param zibo\library\config\io\ini\IniParser $parser Helper for the ini format
     * @param zibo\library\filesystem\File $writePath Path to write new
     * configuration values to
     * @return null
     */
    public function __construct(IniParser $parser, File $writePath = null) {
        $this->parser = $parser;
        $this->paths = array();

        $this->setWritePath($writePath);
    }

    /**
     * Adds a path where configuration files can be found
     * @param File $path
     * @return null
     */
    public function addPath(File $path) {
        $this->paths[] = $path;
    }

    /**
     * Sets the path to write new configuration values to, this will be the
     * last path to be read.
     * @param zibo\library\filesystem\File $path The path to write to
     * @return null
     * @throws zibo\library\config\exception\ConfigException when the provided
     * path is not writable
     */
    public function setWritePath(File $path = null) {
        $this->writePath = $path;
    }

    /**
     * Sets a configuration value to the data source
     * @param string $key The configuration key
     * @param mixed $value The value to write
     * @return null
     * @throws zibo\library\config\exception\ConfigException when the provided
     * key is invalid or empty
     */
    public function set($key, $value) {
        if ($this->writePath === null) {
            throw new ConfigException('No write path set');
        } elseif (!$this->writePath->isWritable()) {
            throw new ConfigException('Write path ' . $this->writePath . ' is not writable');
        }

        if (!is_string($key) || !$key) {
            throw new ConfigException('Provided key is empty');
        }

        $tokens = explode(Config::TOKEN_SEPARATOR, $key);
        if (count($tokens) < 2) {
            throw new ConfigException($key . ' should have at least 2 tokens (eg system.memory). Use ' . Config::TOKEN_SEPARATOR . ' as a token separator.');
        }

        // make sure the write path exists
        $this->writePath->create();

        // gets the file, based on the section of the key
        $fileName = array_shift($tokens) . '.' . self::FILE_EXTENSION;
        $file = new File($this->writePath, $fileName);

        // gets the existing values from the file
        $values = array();
        if ($file->exists()) {
            $this->readFile($values, $file);
        }

        // add the new configuration value
        $key = implode(Config::TOKEN_SEPARATOR, $tokens);
        Config::setValue($values, $key, $value);

        // write the file
        $ini = $this->parser->getIniString($values);

        if ($ini) {
            $file->write($ini);
        } elseif ($file->exists()) {
            $file->delete();
        }
    }

    /**
     * Gets the complete configuration
     * @return array Hierarchic array with each configuration token as a key
     */
    public function getAll() {
        $all = array();

        $sections = $this->getAllSections();
        foreach ($sections as $section) {
            $all[$section] = $this->get($section);
        }

        return $all;
    }

    /**
     * Gets the configuration values for a section
     * @param string $section Name of the section
     * @return array Hierarchic array with each configuration token as a key
     * @throws zibo\library\config\exception\ConfigException when the section
     * name is invalid or empty
     */
    public function get($section) {
        if (!is_string($section) || !$section) {
            throw new ConfigException('Provided section name is empty');
        }

        $fileName = $section . '.' . self::FILE_EXTENSION;
        $config = array();

        $this->readPaths($config, $fileName);

        return $config;
    }

    /**
     * Read the configuration values for all the files with the provided file name in the Zibo file system structure
     * @param array $config Array with the values which are already read
     * @param string $fileName name of the section file eg system.ini
     * @return null
     */
    protected function readPaths(array &$config, $fileName) {
        $paths = $this->paths;
        if ($this->writePath) {
            $paths[] = $this->writePath;
        }

        foreach ($paths as $path) {
            $file = new File($path, $fileName);
            if (!$file->exists()) {
                continue;
            }

            $this->parser->setVariables(array('path' => $path));

            $this->readFile($config, $file);

            $this->parser->setVariables(null);
        }
    }

    /**
     * Read the configuration values for the provided file and add them to the provided values array
     * @param zibo\library\filesystem\File $file file to read and parse
     * @param array $values Array with the values which are already read
     * @return array Values array with the read configuration values added
     * @throws zibo\library\config\exception\ConfigException when the provided
     * file could not be read
     */
    protected function readFile(array &$config, File $file) {
        try {
            $ini = $file->read();

            $this->parser->setIniString($config, $ini);
        } catch (Exception $exception) {
            throw new ConfigException('Could not read the config ' . $file, 0, $exception);
        }
    }

    /**
     * Get the names of all the sections in the configuration
     * @return array Array with the names of all the ini files in the
     * configuration directory, withouth the extension
     */
    public function getAllSections() {
        $sections = array();

        foreach ($this->paths as $path) {
            $sections = $this->getSectionsFromPath($path) + $sections;
        }

        return array_keys($sections);
    }

    /**
     * Get the names of the sections in the provided path
     * @param zibo\library\filesystem\File $path
     * @return array Array with the file names of all the ini files, without
     * the extension, as key
     */
    protected function getSectionsFromPath(File $path) {
        $sections = array();

        if (!$path->exists()) {
            return $sections;
        }

        $files = $path->read();
        foreach ($files as $file) {
            if ($file->isDirectory() || $file->getExtension() != self::FILE_EXTENSION) {
                continue;
            }

            $sectionName = substr($file->getName(), 0, -4);

            $sections[$sectionName] = true;
        }

        return $sections;
    }

}