<?php

namespace zibo\core;

use zibo\library\filesystem\File;

/**
 * Read and write the Zibo bootstrap configuration
 */
class BootstrapConfig {

    /**
     * Name of the default environment
     * @var string
     */
    const DEFAULT_ENVIRONMENT = 'dev';

    /**
     * Path of the zibo.core module
     * @var string
     */
    private $directoryCore;

    /**
     * Path of the public directory
     * @var string
     */
    private $directoryPublic;

    /**
     * Path of the application directory
     * @var string
     */
    private $directoryApplication;

    /**
     * Paths of the modules directories
     * @var array
     */
    private $directoriesModules;

    /**
     * Name of the environment
     * @var string
     */
    private $environment;

    /**
     * Flag to see if the dependencies should be cached
     * @var boolean
     */
    private $cacheDependencies;

    /**
     * Flag to see if the file system should be cached
     * @var boolean
     */
    private $cacheFileSystem;

    /**
     * Flag to see if the parameters should be cached
     * @var boolean
     */
    private $cacheParameters;

    /**
     * Constructs a new Zibo config
     * @param zibo\library\filesystem\File $file Zibo configuration file
     * @return null
     */
    public function __construct() {
        $this->directoryCore = null;
        $this->directoryPublic = null;
        $this->directoryApplication = null;
        $this->directoriesModules = array();
        $this->environment = 'dev';
        $this->cacheDependencies = false;
        $this->cacheFileSystem = false;
        $this->cacheParameters = false;
    }

    /**
     * Sets the core directory
     * @param zibo\library\filesystem\File $directory Path to the directory
     * @return null
     */
    public function setCoreDirectory(File $directory) {
        $this->directoryCore = $directory;
    }

    /**
     * Gets the core directory
     * @return zibo\library\filesystem\File
     */
    public function getCoreDirectory() {
        return $this->directoryCore;
    }

    /**
     * Sets the public directory
     * @param zibo\library\filesystem\File $directory Path to the directory
     * @return null
     */
    public function setPublicDirectory(File $directory) {
        $this->directoryPublic = $directory;
    }

    /**
     * Gets the public directory
     * @return zibo\library\filesystem\File
     */
    public function getPublicDirectory() {
        return $this->directoryPublic;
    }

    /**
     * Sets the application directory
     * @param zibo\library\filesystem\File $directory Path to the directory
     * @return null
     */
    public function setApplicationDirectory(File $directory) {
        $this->directoryApplication = $directory;
    }

    /**
     * Gets the application directory
     * @return zibo\library\filesystem\File
     */
    public function getApplicationDirectory() {
        return $this->directoryApplication;
    }

    /**
     * Adds a modules directory
     * @param zibo\library\filesystem\File $directory Path to the directory
     * @return null
     */
    public function addModulesDirectory(File $directory) {
        $this->directoriesModules[$directory->getPath()] = $directory;
    }

    /**
     * Removes a modules directory
     * @param zibo\library\filesystem\File $directory Path to the directory
     * @return null
     */
    public function removeModulesDirectory(File $directory) {
        $path = $directory->getPath();
        if (isset($this->directoriesModules[$path])) {
            unset($this->directoriesModules[$path]);
        }
    }

    /**
     * Removes all the modules directories
     * @return null
     */
    public function removeModulesDirectories() {
        $this->directoriesModules = array();
    }

    /**
     * Gets the module container directories
     * @return zibo\library\filesystem\File
     */
    public function getModulesDirectories() {
        return $this->directoriesModules;
    }

    /**
     * Gets the environment
     * @return string Name of the environment
     */
    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * Sets the environment
     * @param string $environment Name of the environment
     * @return null
     * @throws zibo\core\build\exception\BuildException when the provided
     * environment is empty or invalid
     */
    public function setEnvironment($environment) {
        if (!is_string($environment) || $environment == '') {
            throw new BuildException('Provided environment is empty or invalid');
        }

        $this->environment = $environment;
    }

    /**
     * Sets whether the dependencies will be cached
     * @param boolean $flag
     * @return null
     */
    public function setWillCacheDependencies($flag) {
        $this->cacheDependencies = $flag;
    }

    /**
     * Gets whether the dependencies will be cached
     * @return string
     */
    public function willCacheDependencies() {
        return $this->cacheDependencies;
    }

    /**
     * Sets whether the file system will be cached
     * @param boolean $flag
     * @return null
     */
    public function setWillCacheFileSystem($flag) {
        $this->cacheFileSystem = $flag;
    }

    /**
     * Gets whether the file system will be cached
     * @return string
     */
    public function willCacheFileSystem() {
        return $this->cacheFileSystem;
    }

    /**
     * Sets whether the parameters will be cached
     * @param boolean $flag
     * @return null
     */
    public function setWillCacheParameters($flag) {
        $this->cacheParameters = $flag;
    }

    /**
     * Gets whether the parameters will be cached
     * @return string
     */
    public function willCacheParameters() {
        return $this->cacheParameters;
    }

    /**
     * Reads the boot configuration file and extracts the configuration values
     * from it
     * @param zibo\library\filesystem\File $file
     * @return null
     */
    public function read(File $file) {
        include $file->getPath();

        if (isset($config['dir']['core'])) {
            $this->directoryCore = new File($config['dir']['core']);
        } else {
            $this->directoryCore = null;
        }

        if (isset($config['dir']['public'])) {
            $this->directoryPublic = new File($config['dir']['public']);
        } else {
            $this->directoryPublic = null;
        }

        if (isset($config['dir']['application'])) {
            $this->directoryApplication = new File($config['dir']['application']);
        } else {
            $this->directoryApplication = null;
        }

        $this->directoriesModules = array();
        if (isset($config['dir']['modules'])) {
            if (is_array($config['dir']['modules'])) {
                foreach ($config['dir']['modules'] as $directory) {
                    $this->directoriesModules[$directory] = new File($directory);
                }
            } else {
                $this->directoriesModules[$config['dir']['modules']] = new File($config['dir']['modules']);
            }
        }

        if (isset($config['environment'])) {
            $this->environment = $config['environment'];
        } else {
            $this->environment = self::DEFAULT_ENVIRONMENT;
        }

        if (isset($config['cache']['dependencies'])) {
            $this->cacheDependencies = $config['cache']['dependencies'];
        } else {
            $this->cacheDependencies = false;
        }

        if (isset($config['cache']['filesystem'])) {
            $this->cacheFileSystem = $config['cache']['filesystem'];
        } else {
            $this->cacheParameters = false;
        }

        if (isset($config['cache']['parameters'])) {
            $this->cacheParameters = $config['cache']['parameters'];
        } else {
            $this->cacheParameters = false;
        }
    }

    /**
     * Writes the boot configuration to file
     * @param zibo\library\filesystem\File $file
     * @return null
     */
    public function write(File $file) {
        $config = array(
            'environment' => $this->environment,
            'dir' => array(),
            'cache' => array(
                'dependencies' => $this->cacheDependencies,
                'filesystem' => $this->cacheFileSystem,
                'parameters' => $this->cacheParameters,
            ),
        );

        if ($this->directoryCore) {
            $config['dir']['core'] = $this->directoryCore->getPath();
        }
        if ($this->directoryPublic) {
            $config['dir']['public'] = $this->directoryPublic->getPath();
        }
        if ($this->directoryApplication) {
            $config['dir']['application'] = $this->directoryApplication->getPath();
        }
        if ($this->directoriesModules) {
            if (is_array($this->directoriesModules)) {
                $config['dir']['modules'] = array();
                foreach ($this->directoriesModules as $directoryModules) {
                    $config['dir']['modules'][] = $directoryModules->getPath();
                }
            } else {
                $config['dir']['modules'] = $this->directoriesModules->getPath();
            }
        }

        $output = "<?php\n\n";
        $output .= "/*\n";
        $output .= " * This file is generated by zibo\core\build\Config.\n";
        $output .= " */\n";
        $output .= "\n";
        $output .= '$config = ' . var_export($config, true) . ";\n";

        $parent = $file->getParent();
        $parent->create();

        $file->write($output);
    }

}