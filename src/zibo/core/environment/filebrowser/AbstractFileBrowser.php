<?php

namespace zibo\core\environment\filebrowser;

use zibo\core\Zibo;

use zibo\library\filesystem\exception\FileSystemException;
use zibo\library\filesystem\File;

/**
 * Abstract file browser to find files in the Zibo filesystem structure
 */
abstract class AbstractFileBrowser implements FileBrowser {

    /**
     * The public directory
     * @var zibo\library\filesystem\File
     */
    protected $publicDirectory;

    /**
     * The application directory
     * @var zibo\library\filesystem\File
     */
    protected $applicationDirectory;

    /**
     * The modules directories
     * @var array
     */
    protected $modulesDirectories = array();

    /**
     * Array containing the directories of the Zibo filesystem structure
     * @var array
     */
    protected $includeDirectories;

    /**
     * Sets the public directory
     * @param string|zibo\library\filesystem\File $directory
     * @return null
     */
    public function setPublicDirectory($directory) {
        $this->publicDirectory = new File($directory);
    }

    /**
     * Gets the public directory
     * @return zibo\library\filesystem\File
     */
    public function getPublicDirectory() {
        return $this->publicDirectory;
    }

    /**
     * Sets the application directory
     * @param string|zibo\library\filesystem\File $directory
     * @return null
     */
    public function setApplicationDirectory($directory) {
        $this->applicationDirectory = new File($directory);
    }

    /**
     * Gets the application directory
     * @return zibo\library\filesystem\File
     */
    public function getApplicationDirectory() {
        return $this->applicationDirectory;
    }

    /**
     * Adds a module directory.
     *
     * A module directory contains multiple modules and not a single module
     * @param string|zibo\library\filesystem\File $directory
     * @return null
     */
    public function addModulesDirectory($directory) {
        $directory = new File($directory);
        $this->modulesDirectories[$directory->getPath()] = $directory;
    }

    /**
     * Removes a module directory
     * @param string|zibo\library\filesystem\File $directory
     * @return null
     */
    public function removeModulesDirectory($directory) {
        $directory = new File($directory);
        $directory = $directory->getPath();

        if (isset($this->modulesDirectories[$directory])) {
            unset($this->modulesDirectories[$directory]);
        }
    }

    /**
     * Gets all the module directories
     * @return array Array with File instances
     */
    public function getModulesDirectories() {
        return $this->modulesDirectories;
    }

    /**
     * Gets the base paths of the Zibo filesystem structure. This will return
     * the path of application, the modules and system.
     * @param boolean $refresh set to true to reread the include paths
     * @return array array with File instances
     */
    public function getIncludeDirectories($refresh = false) {
        if ($this->includeDirectories && !$refresh) {
            return $this->includeDirectories;
        }

        $this->includeDirectories = array();
        $this->includeDirectories[] = $this->applicationDirectory;

        foreach ($this->modulesDirectories as $modulesDirectory) {
            $moduleDirectories = array();

            $moduleFiles = $modulesDirectory->read();
            foreach ($moduleFiles as $moduleFile) {
                if (!$moduleFile->isPhar() && !$moduleFile->isDirectory()) {
                    continue;
                }

                $moduleDirectories[$moduleFile->getPath()] = $moduleFile;
            }

            ksort($moduleDirectories);

            foreach ($moduleDirectories as $moduleDirectory) {
                $this->includeDirectories[] = $moduleDirectory;
            }
        }

        return $this->includeDirectories;
    }

    /**
     * Gets the relative file in the file system structure for a given
     * absolute file.
     * @param string|zibo\library\filesystem\File $file Path to a file to get
     * the relative file from
     * @return zibo\library\filesystem\File relative file in the file system
     * structure
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * provided file is not part of the file system structure
     */
    public function getRelativeFile($file) {
        $file = new File($file);
        $absoluteFile = $file->getAbsolutePath();

        $isPhar = $file->hasPharProtocol();
        if ($isPhar) {
            $absoluteFile = substr($absoluteFile, 7);
        }

        $includeDirectories = $this->getIncludeDirectories();
        foreach ($includeDirectories as $includeDirectory) {
            $includeAbsolutePath = $includeDirectory->getAbsolutePath();
            if (strpos($absoluteFile, $includeAbsolutePath) !== 0) {
                continue;
            }

            return new File(str_replace($includeAbsolutePath . File::DIRECTORY_SEPARATOR, '', $absoluteFile));
        }

        throw new FileSystemException($file . ' is not in the file system structure');
    }

    /**
     * Gets the first file in the Zibo filesystem structure according to the
     * provided path.
     * @param string $file Relative path of a file in the Zibo filesystem
     * structure
     * @return zibo\library\filesystem\File|null Instance of the file if found,
     * null otherwise
     */
    public function getFile($file) {
        return $this->lookupFile($file, true);
    }

    /**
     * Gets all the files in the Zibo filesystem structure according to the
     * provided path.
     * @param string $file Relative path of a file in the Zibo filesystem
     * structure
     * @return array array with File instances
     * @see zibo\library\filesystem\File
     */
    public function getFiles($file) {
        return $this->lookupFile($file, false);
    }

    /**
     * Look for files
     * @param string $file Relative path of a file in the Zibo filesystem
     * structure
     * @param boolean $firstOnly true to get the first matched file, false
     * to get an array with all the matched files
     * @return zibo\library\filesystem\File|array Depending on the firstOnly
     * flag, an instance or an array of File
     * @throws zibo\library\filesystem\exception\FileSystemException when file
     * is empty or not a string
     */
    abstract protected function lookupFile($file, $firstOnly);

    /**
     * Resets the browser
     * @return null
     */
    public function reset() {
        $this->includeDirectories = null;
    }

}