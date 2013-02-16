<?php

namespace zibo\core;

use zibo\core\cache\classes\ClassMinifier;
use zibo\core\environment\config\ZiboIniConfigIO;
use zibo\core\environment\dependency\io\ZiboXmlDependencyIO;
use zibo\core\environment\filebrowser\FileBrowser;
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
     * Path to the config of the classes cache
     * @var string
     */
    const FILE_CLASSES_CONFIG = 'config/classes.conf';

    /**
     * Path to the classes cache file
     * @var string
     */
    const FILE_CLASSES_CACHE = '/data/cache/classes.php';

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
     *         'classes' => false, // flag to cache the common classes (bool)
     *         'dependencies' => false, // flag to cache the dependencies (bool)
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
        $this->willCacheClasses = false;
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

        if (isset($config['cache']['classes'])) {
            $this->willCacheClasses = $config['cache']['classes'];
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
        $classesFile = $this->applicationDirectory . self::FILE_CLASSES_CACHE;
        if (file_exists($classesFile)) {
            // load common classes at once
            require_once $classesFile;

            $loadTmpAutoloader = false;
        } else {
            // load every class individually
            $this->loadClass('zibo\\library\\ErrorHandler');
            $this->loadClass('zibo\\library\\Autoloader');

            $loadTmpAutoloader = true;
        }

        // register the error handler to convert errors into exceptions
        $errorHandler = new ErrorHandler();
        $errorHandler->registerErrorHandler();

        if ($loadTmpAutoloader) {
            // register a temporary autoloader and add the src of system to it
            $autoloader = new Autoloader();
            $autoloader->addIncludePath($this->coreDirectory . '/src');
            $autoloader->registerAutoLoader();
        }

        // create the file browser
        $this->loadClass('zibo\\library\\filesystem\\exception\\FileSystemException');
        $fileBrowser = $this->createFileBrowser();

        // register the Zibo autoloader
        $ziboAutoloader = new ZiboAutoloader($fileBrowser);
        $ziboAutoloader->registerAutoloader();

        // unregister the temporary autoloader
        if ($loadTmpAutoloader) {
            $autoloader->unregisterAutoloader();
        }

        // write the classes cache if needed
        if ($this->willCacheClasses && $loadTmpAutoloader) {
            $this->writeClassesCache($fileBrowser);
        }

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
     * Writes the classes cache
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @return null
     */
    protected function writeClassesCache(FileBrowser $fileBrowser) {
        $classes = $this->readClasses($fileBrowser);

        $minifier = new ClassMinifier();
        $source = $minifier->minify($classes);

        $file = new File($this->applicationDirectory . self::FILE_CLASSES_CACHE);
        $file->getParent()->create();
        $file->write($source);
    }

    /**
     * Read the classes from config/classes.conf
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @return array
     */
    protected function readClasses(FileBrowser $fileBrowser) {
        $classes = array();

        $files = array_reverse($fileBrowser->getFiles(self::FILE_CLASSES_CONFIG));
        foreach ($files as $file) {
            $content = $file->read();

            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);

                if (!$line || substr($line, 0, 1) == '#' || substr($line, 0, 1) == ';') {
                    continue;
                }

                $classes[$line] = true;
            }
        }

        return array_keys($classes);
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