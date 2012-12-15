<?php

namespace zibo\core;

use zibo\core\environment\config\ZiboIniConfigIO;
use zibo\core\environment\dependency\io\ZiboXmlDependencyIO;
use zibo\core\environment\filebrowser\FileBrowser;
use zibo\core\environment\filebrowser\IndexedFileBrowser;
use zibo\core\environment\filebrowser\ZiboFileBrowser;
use zibo\core\environment\Autoloader as ZiboAutoloader;
use zibo\core\environment\Environment;

use zibo\library\config\io\CachedConfigIO;
use zibo\library\dependency\io\CachedDependencyIO;
use zibo\library\filesystem\File;
use zibo\library\mvc\view\ExceptionView;
use zibo\library\Autoloader;
use zibo\library\ErrorHandler;

use \Exception;

/**
 * The bootstrap of Zibo
 */
class Bootstrap {

    /**
     * Constructs a new bootstrap
     * @param array $config Configuration values for the bootstrap
     *
     * <p>The possible values are explained through following output: (required values are tagged with *)</p>
     * <pre>
     * $config = array(
     *     'environment' => 'dev', // name of the environment
     *     'dir' => array(
     *         'core' => null, // path of the zibo.core module (string) *
     *         'public' => null, // path of the public directory (string) *
     *         'application' => null, // path of the application directory (string) *
     *         'modules' => null, // path(s) to the module container directories (null|string|array)
     *     ),
     *     'cache' => array(
     *         'dependencies' => false, // flag to cache the dependencies (bool)
     *         'filesystem' => false, // flag to cache the filesystem (bool)
     *         'parameters' => false, // flag to cache the parameters (bool)
 	 *     ),
 	 *     'sapi' => null // full class name of the sapi (string)
     * );
     * </pre>
     * @return null
     * @throws Exception when a required variable is not set
     */
    public function __construct(array $config) {
        $this->environment = 'dev';
        $this->coreDirectory = null;
        $this->publicDirectory = null;
        $this->applicationDirectory = null;
        $this->modulesDirectories = null;
        $this->willCacheDependencies = false;
        $this->willCacheFileSystem = false;
        $this->willCacheParameters = false;
        $this->sapi = null;
        $this->fileBrowser = null;

        if (isset($config['environment'])) {
            $this->environment = $config['environment'];
        }

        if (isset($config['dir']['core'])) {
            $this->coreDirectory = $config['dir']['core'];
        }

        if (isset($config['dir']['public'])) {
            $this->publicDirectory = $config['dir']['public'];
        }

        if (isset($config['dir']['application'])) {
            $this->applicationDirectory = $config['dir']['application'];
        }

        if (isset($config['dir']['modules'])) {
            $this->modulesDirectories = $config['dir']['modules'];
        }

        if (isset($config['cache']['dependencies'])) {
            $this->willCacheDependencies = $config['cache']['dependencies'];
        }

        if (isset($config['cache']['filesystem'])) {
            $this->willCacheFileSystem = $config['cache']['filesystem'];
        }

        if (isset($config['cache']['parameters'])) {
            $this->willCacheParameters = $config['cache']['parameters'];
        }

        if (isset($config['sapi'])) {
            $this->sapi = $config['sapi'];
        }

        // make sure the directories are set
        if (!$this->coreDirectory) {
            throw new Exception('No core directory set');
        }
        if (!$this->publicDirectory) {
            throw new Exception('No public directory set');
        }
        if (!$this->applicationDirectory) {
            throw new Exception('No application directory set');
        }
    }

    /**
     * Creates an exception view if possible
     * @param Exception $exception
     * @return zibo\library\mvc\view\ExceptionView|null
     */
    public function createExceptionView(Exception $exception) {
        try {
            $this->loadClass('zibo\\library\\String');
            $this->loadClass('zibo\\library\\mvc\\view\\View');
            $this->loadClass('zibo\\library\\mvc\\view\\ExceptionView');

            return new ExceptionView($exception);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Boots an instance of Zibo
     * @return Zibo
     */
    public function boot() {
        // include the error handler and the autoloader
        $this->loadClass('zibo\\library\\ErrorHandler');
        $this->loadClass('zibo\\library\\Autoloader');

        // register the error handler to convert errors into exceptions
        $errorHandler = new ErrorHandler();
        $errorHandler->registerErrorHandler();

        // register a basic autoloader and add the src of system to it
        $autoloader = new Autoloader();
        $autoloader->addIncludePath($this->coreDirectory . '/src');
        $autoloader->registerAutoLoader();

        // create the file browser
        $this->loadClass('zibo\\library\\filesystem\\exception\\FileSystemException');
        $fileBrowser = $this->createFileBrowser();

        // register the Zibo autoloader
        $ziboAutoloader = new ZiboAutoloader($fileBrowser);
        $ziboAutoloader->registerAutoloader();

        // unregister the basic autoloader
        $autoloader->unregisterAutoloader();

        // create the environment
        $parametersIO = $this->createParametersIO($fileBrowser);
        $dependencyIO = $this->createDependencyIO($fileBrowser);
        $sapi = $this->createSapi();
        $environment = new Environment($this->environment, $fileBrowser, $parametersIO, $dependencyIO, $sapi);

        // create a Zibo instance from the environment and boot it
        $zibo = new Zibo($environment);
        $zibo->bootModules();

        // return the Zibo instance ready to use
        return $zibo;
    }

    /**
     * Creates a instance of the FileBrowser
     * @return zibo\core\environment\filebrowser\FileBrowser
     */
    public function createFileBrowser() {
        $fileBrowser = new ZiboFileBrowser();

        $fileBrowser->setPublicDirectory($this->publicDirectory);
        $fileBrowser->setApplicationDirectory($this->applicationDirectory);

        if ($this->modulesDirectories) {
            if (is_array($this->modulesDirectories)) {
                foreach ($this->modulesDirectories as $modulesDirectory) {
                    $fileBrowser->addModulesDirectory($modulesDirectory);
                }
            } else {
                $fileBrowser->addModulesDirectory($this->modulesDirectories);
            }
        }

        if ($this->willCacheFileSystem) {
            $indexFile = new File($this->applicationDirectory, Zibo::DIRECTORY_DATA . '/' . Zibo::DIRECTORY_CACHE . '/filesystem.php');
            $fileBrowser = new IndexedFileBrowser($indexFile, $fileBrowser);
        }

        return $fileBrowser;
    }

    /**
     * Creates the I/O implementation for the parameters
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @return zibo\library\config\io\ConfigIO
     */
    public function createParametersIO(FileBrowser $fileBrowser) {
        $parametersIO = new ZiboIniConfigIO($fileBrowser);
        $parametersIO->setEnvironment($this->environment);

        if ($this->willCacheParameters) {
            $cacheFile = Zibo::DIRECTORY_DATA . '/' . Zibo::DIRECTORY_CACHE . '/parameters-' . $this->environment . '.php';
            $cacheFile = new File($this->applicationDirectory, $cacheFile);
            $parametersIO = new CachedConfigIO($parametersIO, $cacheFile);
        }

        return $parametersIO;
    }

    /**
     * Creates the I/O implementation to obtain the dependency container
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @return zibo\library\dependency\io\DependencyIO
     */
    public function createDependencyIO(FileBrowser $fileBrowser) {
        $dependencyIO = new ZiboXmlDependencyIO($fileBrowser, $this->environment);

        if ($this->willCacheDependencies) {
            $cacheFile = Zibo::DIRECTORY_DATA . '/' . Zibo::DIRECTORY_CACHE . '/dependencies-' . $this->environment . '.php';
            $cacheFile = new File($this->applicationDirectory, $cacheFile);
            $dependencyIO = new CachedDependencyIO($dependencyIO, $cacheFile);
        }

        return $dependencyIO;
    }

    /**
     * Creates the SAPI
     * @return zibo\core\environment\sapi\Sapi|null A SAPI instance if set,
     * null otherwise
     */
    public function createSapi() {
        if (!$this->sapi) {
            return null;
        }

        return new $this->sapi;
    }

    /**
     * Loads the class from the zibo.core module
     * @param string $className Full class name
     * @return null
     * @throws Exception when the class does not exist
     */
    protected function loadClass($className) {
        $file = $this->coreDirectory . '/src/' . str_replace('\\', '/', $className) . '.php';
        if (!file_exists($file)) {
            throw new Exception('Could not load class ' . $className . ': ' . $file . ' does not exist');
        }

        require_once $file;

        if (!class_exists($className) && !interface_exists($className)) {
            throw new Exception('Could not load class ' . $className . ': ' . $file . ' does not contain the provided class');
        }
    }

}