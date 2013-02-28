<?php

namespace zibo\core\build;

use zibo\core\build\exception\BuildException;
use zibo\core\BootstrapConfig;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

/**
 * Builder of your Zibo installation
 */
class Builder {

    /**
     * Id of the default handler
     * @var string
     */
    const DEFAULT_HANDLER = 'default';

    /**
     * Name of the application directory
     * @var string
     */
    const DIRECTORY_APPLICATION = 'application';

    /**
     * Build directory for the application files
     * @var zibo\library\filesystem\File
     */
    private $application;

    /**
     * Build directory for the public files
     * @var zibo\library\filesystem\File
     */
    private $public;

    /**
     * Directories to exclude from the build
     * @var array
     */
    private $exclude = array(
        '.git' => true,
        '.gitignore' => true,
        '.settings' => true,
        '.svn' => true,
        'smarty/development' => true,
        'smarty/documentation' => true,
        'application/log' => true,
        'test' => true,
    );

    /**
     * Builds your current Zibo installation into the most performant state,
     * ready for production
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @param zibo\library\filesystem\File $destination Destination of the
     * build
     * @param boolean $cleanUp Set to false to keep everything in the
     * destination directory and only overwrite the necessairy files
     * @return null
     */
    public function build(Zibo $zibo, File $destination, $environment = 'prod', $cleanUp = true) {
        $this->prepareDestination($destination, $cleanUp);

        echo "Copying files...\n";

        $handlers = $zibo->getDependencies('zibo\\core\\build\\handler\\DirectoryHandler');

        $fileBrowser = $zibo->getEnvironment()->getFileBrowser();
        $includeDirectories = array_reverse($fileBrowser->getIncludeDirectories());
        foreach ($includeDirectories as $includeDirectory) {
            $moduleDirectories = $includeDirectory->read();
            foreach ($moduleDirectories as $module) {
                if (!$module->isDirectory()) {
                    continue;
                }

                $name = $module->getName();

                if (isset($this->exclude[$name])) {
                    continue;
                }

                if ($name == Zibo::DIRECTORY_PUBLIC) {
                    $directoryDestination = new File($this->public);
                } else {
                    $directoryDestination = new File($this->application, $name);
                }
                $directoryDestination->create();

                if (isset($handlers[$name])) {
                    $handler = $handlers[$name];
                } else {
                    $handler = $handlers[self::DEFAULT_HANDLER];
                }
                $handler->handleDirectory($module, $directoryDestination, $this->exclude);
            }
        }

        // copy the public directory
        $handlers[self::DEFAULT_HANDLER]->handleDirectory($fileBrowser->getPublicDirectory(), $this->public, $this->exclude);

        echo "Clearing caches\n";

        // clear cache
        $cacheDirectory = new File($this->application, Zibo::DIRECTORY_DATA . '/' . Zibo::DIRECTORY_CACHE);
        if ($cacheDirectory->exists()) {
            $cacheDirectory->delete();
        }

        $cacheDirectory = new File($this->public, Zibo::DIRECTORY_CACHE);
        if ($cacheDirectory->exists()) {
            $files = $cacheDirectory->read();
            foreach ($files as $file) {
                $file->delete();
            }
        }

        echo "Configuring the scripts\n";

        // copy htaccess
        $htaccessFile = new File(__DIR__ . '/../../../../.htaccess');
        $htaccessFile->copy(new File($this->public, '.htaccess'));

        // copy console
        $newConsoleFile = new File($this->application, 'console.php');
        $consoleFile = new File(__DIR__ . '/../../../console.php');
        $consoleFile->copy($newConsoleFile);

        // copy index
        $newIndexFile = new File($this->public, 'index.php');
        $indexFile = new File(__DIR__ . '/../../../index.php');
        $indexFile->copy($newIndexFile);

        // set Zibo config
        $newConfigFile = new File($this->application, BootstrapConfig::SCRIPT_CONFIG);
        $configFile = new File(ZIBO_CONFIG);

        $config = new BootstrapConfig();
        $config->read($configFile);
        $config->setApplicationDirectory($this->application);
        $config->setCoreDirectory($this->application);
        $config->setPublicDirectory($this->public);
        $config->removeModulesDirectories();
        $config->setEnvironment($environment);
        $config->setWillCacheClasses(true);
        $config->setWillCacheDependencies(true);
        $config->setWillCacheParameters(true);
        $config->write($newConfigFile);

        // link the scripts with the config
        $configPath = $newConfigFile->getAbsolutePath();

        $config->updateScript($newConsoleFile, $configPath);
        $config->updateScript($newIndexFile, $configPath);
    }

    /**
     * Prepares the build directories
     * @param zibo\library\filesystem\File $destination Build destination
     * @param boolean $cleanUp Flag to skip cleaning up
     * @return null
     * @throw zibo\core\build\exception\BuildException when the destination
     * is not a directory or is not writable
     */
    protected function prepareDestination(File $destination, $cleanUp = true) {
        if ($destination->exists()) {
            if (!$destination->isDirectory()) {
                throw new BuildException('Destination ' . $destination . ' is not a directory');
            } elseif (!$destination->isWritable()) {
                throw new BuildException('Destination ' . $destination . ' is not a writable');
            }

            $files = $destination->read();
            if ($cleanUp && $files) {
                echo 'Cleaned ' . $destination . "\n";

                $destination->delete();
            }
        }

        $this->application = new File($destination, self::DIRECTORY_APPLICATION);
        $this->application->create();

        $log = new File($this->application, 'log');
        $log->create();

        echo 'Created ' . $this->application . "\n";

        $this->public = new File($destination, Zibo::DIRECTORY_PUBLIC);
        $this->public->create();

        echo 'Created ' . $this->public . "\n";
    }

}