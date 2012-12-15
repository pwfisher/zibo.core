<?php

namespace zibo\core\environment\config;

use zibo\core\environment\filebrowser\FileBrowser;
use zibo\core\Zibo;

use zibo\library\config\io\ini\IniConfigIO;
use zibo\library\config\io\ini\IniParser;
use zibo\library\filesystem\File;

/**
 * Implementation for the configuration I/O based on the Zibo file system
 * structure
 */
class ZiboIniConfigIO extends IniConfigIO implements ZiboConfigIO {

    /**
     * Instance of the file browser
     * @var zibo\core\environment\filebrowser\FileBrowser
     */
    private $fileBrowser;

    /**
     * Name of the environment
     * @var string
     */
    private $environment;

    /**
     * Constructs a new Zibo configuration I/O
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @param string $environment Name of the environment
     * @param string $sapi Name of the SAPI
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser) {
        $this->fileBrowser = $fileBrowser;

        $parser = new IniParser();

        $writePath = new File($this->fileBrowser->getApplicationDirectory(), Zibo::DIRECTORY_CONFIG);

        parent::__construct($parser, $writePath);
    }

    /**
     * Sets the name of the environment
     * @param string $environment Name of the environment
     * @return null
     * @throws Exception when the provided name is empty or not a string
     */
    public function setEnvironment($environment = null) {
        if ($environment !== null && (!is_string($environment) || !$environment)) {
            throw new Exception('Provided environment is empty or not a string');
        }

        $this->environment = $environment;
    }

    /**
     * Read the configuration values for all the files with the provided file
     * name in the Zibo file system structure
     * @param array $config Array with the values which are already read
     * @param string $fileName name of the section file eg system.ini
     * @return null
     */
    protected function readPaths(array &$config, $fileName) {
        $configPath = Zibo::DIRECTORY_CONFIG . File::DIRECTORY_SEPARATOR;
        $configFileName = $configPath . $fileName;

        $variables = array(
            'application' => $this->fileBrowser->getApplicationDirectory()->getPath(),
            'environment' => $this->environment,
            'path' => null,
            'public' => $this->fileBrowser->getPublicDirectory()->getPath(),
        );

        $this->readFiles($config, $configFileName, $variables);

        if ($this->environment) {
            $environmentPath = $configPath . $this->environment . File::DIRECTORY_SEPARATOR;
            $environmentFileName = $environmentPath . $fileName;

            $this->readFiles($config, $environmentFileName, $variables);
        }
    }

    /**
     * Reads the configuration files with the provided file name
     * @param array $config The configuration to set the result to
     * @param string $fileName The relative file name of the configuration
     * @param array $variables The variables for the configuration values
     * @return null
     */
    protected function readFiles(array &$config, $fileName, array $variables) {
        $files = array_reverse($this->fileBrowser->getFiles($fileName));
        foreach ($files as $file) {
            $path = str_replace(File::DIRECTORY_SEPARATOR . $fileName, '', $file->getPath());

            $variables['path'] = $path;

            $this->parser->setVariables($variables);

            $this->readFile($config, $file);

            $this->parser->setVariables(null);
        }
    }

    /**
     * Get the names of all the sections in the configuration
     * @return array Array with the names of all the ini files in the
     * configuration directory, withouth the extension
     */
    public function getAllSections() {
        $sections = array();

        $includeDirectories = $this->fileBrowser->getIncludeDirectories();
        foreach ($includeDirectories as $directory) {
            $configPath = new File($directory, Zibo::DIRECTORY_CONFIG);
            $sections = $this->getSectionsFromPath($configPath) + $sections;
            $sections = $this->getSectionsFromPath(new File($configPath, $this->environment)) + $sections;
        }

        return array_keys($sections);
    }

}