<?php

namespace zibo\core\environment;

use zibo\core\environment\config\ZiboConfigIO;
use zibo\core\environment\filebrowser\FileBrowser;
use zibo\core\environment\sapi\CliSapi;
use zibo\core\environment\sapi\Sapi;
use zibo\core\environment\sapi\WebSapi;

use zibo\library\config\io\ConfigIO;
use zibo\library\config\Config;
use zibo\library\dependency\io\DependencyIO;
use zibo\library\dependency\DependencyContainer;

use \Exception;

/**
 * The environment Zibo lives in. This object defines the implementation of PHP
 * with the server. The environment also provides the configuration of Zibo.
 */
class Environment {

    /**
     * The name of the environment
     * @var string
     */
    protected $name;

    /**
     * Implementation of the file browser
     * @var zibo\core\environment\filebrowser\FileBrowser
     */
    protected $fileBrowser;

    /**
     * The I/O implementation of the configuration
     * @var zibo\library\config\io\ConfigIO
     */
    protected $configIO;

    /**
     * The configuration
     * @var zibo\library\config\Config
     */
    protected $config;

    /**
     * The dependency IO to obtain a container
     * @var zibo\library\dependency\io\DependencyIO
     */
    protected $dependencyIO;

    /**
     * The dependency container for this environment
     * @var zibo\library\dependency\DependencyContainer
     */
    protected $dependencyContainer;

    /**
     * Implementation of the server interface with PHP
     * @var zibo\core\environment\sapi\Sapi
     */
    protected $sapi;

    /**
     * Constructs a new environment
     * @param string $name The name of the environment (prod, dev, test, ...)
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @param zibo\library\config\io\ConfigIO $configIO
     * @param zibo\core\environment\sapi\Sapi $sapi
     * @return null
     */
    public function __construct($name, FileBrowser $fileBrowser, ConfigIO $configIO, DependencyIO $dependencyIO = null, Sapi $sapi = null) {
        $this->setName($name);
        $this->setSapi($sapi);

        $this->fileBrowser = $fileBrowser;
        $this->configIO = $configIO;
        $this->dependencyIO = $dependencyIO;
    }

    /**
     * Sets the name of this environment
     * @param string $name
     * @throws Exception when the provided name is empty or not a string
     */
    protected function setName($name) {
        if (!is_string($name) || !$name) {
            throw new Exception('Provided environment name is invalid');
        }

        $this->name = $name;
    }

    /**
     * Gets the name of this environment
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Gets the file browser of the environment
     * @return zibo\core\environment\filebrowser\FileBrowser
     */
    public function getFileBrowser() {
        return $this->fileBrowser;
    }

    /**
     * Gets the IO implementation of the configuration
     * @return zibo\library\config\io\ConfigIO
     */
    public function getConfigIO() {
        return $this->configIO;
    }

    /**
     * Gets the configuration
     * @return zibo\library\config\Config
     */
    public function getConfig() {
        if ($this->config) {
            return $this->config;
        }

        if ($this->configIO instanceof ZiboConfigIO) {
            $this->configIO->setEnvironment($this->name);
        }

        return $this->config = new Config($this->configIO);
    }

    /**
     * Gets the I/O implementation of the dependencies
     * @return zibo\library\dependency\io\DependencyIO
     */
    public function getDependencyIO() {
        return $this->dependencyIO;
    }

    /**
     * Sets the dependency container
     * @param zibo\library\dependency\DependencyContainer $container
     */
    public function setDependencyContainer(DependencyContainer $container) {
        $this->dependencyContainer = $container;
    }

    /**
     * Gets the dependency container
     * @return zibo\library\dependency\DependencyContainer
     */
    public function getDependencyContainer() {
        if (!$this->dependencyContainer) {
            if ($this->dependencyIO) {
                $this->dependencyContainer = $this->dependencyIO->getContainer();
            } else {
                $this->dependencyContainer = new DependencyContainer();
            }
        }

        return $this->dependencyContainer;
    }

    /**
     * Sets the implementation of PHP with the server
     * @param zibo\core\environment\sapi\Sapi|null $sapi
     * @return null
     */
    protected function setSapi($sapi = null) {
        if ($sapi === null) {
            if ($this->isCli()) {
                $sapi = new CliSapi();
            } else {
                $sapi = new WebSapi();
            }
        }

        $this->sapi = $sapi;
    }

    /**
     * Gets the implementation of PHP with the server
     * @return zibo\core\environment\sapi\Sapi
     */
    public function getSapi() {
        return $this->sapi;
    }

    /**
     * Checks if the current environment is CLI
     * @return boolean
     */
    public function isCli() {
        return PHP_SAPI == 'cli';
    }

}