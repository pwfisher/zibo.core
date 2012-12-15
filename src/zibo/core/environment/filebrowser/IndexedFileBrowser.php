<?php

namespace zibo\core\environment\filebrowser;

use zibo\library\filesystem\exception\FileSystemException;
use zibo\library\filesystem\File;

/**
 * Indexed browser to find files in the Zibo filesystem structure
 */
class IndexedFileBrowser extends AbstractFileBrowser {

    /**
     * index of the filesystem with as key the relative filename and as value
     * an array with path's where this file can be found
     * @var array
     */
    protected $index;

    /**
     * The file for the index
     * @var zibo\library\filesystem\File
     */
    protected $indexFile;

    /**
     * array with filenames which are ignored while indexing
     * @var array
     */
    protected $exclude;

    /**
     * Another file browser to wrap around, when a file could not be found,
     * the wrapped file browser will be polled
     * @var FileBrowser
     */
    private $fileBrowser;

    /**
     * Creates a new indexed file browser
     * @param zibo\library\filesystem\File $file The file to store the index to
     * @param FileBrowser $fileBrowser Another file browser to wrap around, when
     * a file could not be found, the wrapped file browser will be polled
     * @return null
     */
    public function __construct(File $file, FileBrowser $fileBrowser = null) {
        $this->index = null;
        $this->indexFile = $file;
        $this->exclude = array(
    		'.svn' => null,
    		'data/cache' => null,
    		'data/session' => null,
    		'public' => null,
    		'test' => null,
        );

        if (!$fileBrowser) {
            $this->fileBrowser = null;
            return;
        }

        $this->fileBrowser = $fileBrowser;
        $this->publicDirectory = $this->fileBrowser->getPublicDirectory();
        $this->applicationDirectory = $this->fileBrowser->getApplicationDirectory();

        if (!$this->fileBrowser instanceof AbstractFileBrowser) {
            return;
        }

        $this->modulesDirectories = $this->fileBrowser->getModulesDirectories();
    }

    /**
     * Look for files in the index
     * @param string $fileName relative path of a file in the Zibo filesystem structure
     * @param boolean $firstOnly true to get the first matched file, false to get an array
     *                           with all the matched files
     * @return zibo\library\filesystem\File|array Depending on the firstOnly flag, an instance or an array of zibo\library\filesystem\File
     * @throws zibo\ZiboException when fileName is empty or not a string
     */
    protected function lookupFile($fileName, $firstOnly) {
        if ($fileName instanceof File) {
            $fileName = $fileName->getPath();
        }

        if ($this->index === null) {
            $this->initialize();
        }

        if (!isset($this->index[$fileName])) {
            // no file found, check for a wrapped filebrowser
            if ($firstOnly) {
                if ($this->fileBrowser) {
                    $result = $this->fileBrowser->getFile($fileName);
                } else {
                    $result = null;
                }
            } else {
                if ($this->fileBrowser) {
                    $result = $this->fileBrowser->getFiles($fileName);
                } else {
                    $result = array();
                }
            }

            if ($result) {
                // index out of date
                $this->clear();
            }

            return $result;
        }

        if ($firstOnly) {
            // gets the first file
            reset($this->index[$fileName]);
            $file = each($this->index[$fileName]);
            return new File($file['value']);
        }

        // gets an array of files
        $files = array();
        foreach ($this->index[$fileName] as $file) {
            $files[] = new File($file);
        }

        return $files;
    }

    /**
     * Gets the index of this browser
     * @return array Array with the relative path of a file as key and an array with the full paths of all matching
     * 				 files throughout the application, modules and system as value
     */
    public function getIndex() {
    	return $this->index;
    }

    /**
     * Sets the excluded directories for this file browser
     * @param array $exclude Array with the relative paths of the excluded directories
     * @return null
     */
    public function setExclude(array $exclude) {
        $this->exclude = array();

        foreach ($exclude as $name) {
            $this->exclude[$name] = null;
        }
    }

    /**
     * Gets the excluded directories of this file browser
     * @return array
     */
    public function getExclude() {
    	return array_keys($this->exclude);
    }

    /**
     * Clears the index file
     * @return null
     */
    public function clear() {
        if ($this->indexFile->exists()) {
            $this->indexFile->delete();
        }
    }

    /**
     * Initializes the index
     * @return null;
     */
    protected function initialize() {
        if ($this->indexFile->isLocked()) {
            $this->indexFile->waitForUnlock();
        }

        if (!$this->indexFile->exists()) {
            $this->reset();
            return;
        }

        include $this->indexFile->getPath();

        $this->index = $index;
    }

    /**
     * Reset the browser by creating a new index of the files according to the
     * Zibo filesystem structure
     * @return null
     */
    public function reset() {
        if ($this->indexFile->isLocked()) {
            $this->initialize();
            return;
        }

        $this->indexFile->lock();

        parent::reset();

        $this->includeDirectories = $this->getIncludeDirectories();
        $this->index = array();

        foreach ($this->includeDirectories as $includeDirectory) {
            $this->indexDirectory($includeDirectory);
        }

        $parentDirectory = $this->indexFile->getParent();
        $parentDirectory->create();

        $php = $this->generatePhp($this->index);

        $this->indexFile->write($php);
        $this->indexFile->unlock();
    }

    /**
     * Add the files of a directory recursively to the index
     * @param zibo\library\filesystem\File $path
     * @param string $prefix
     * @return null
     */
    private function indexDirectory(File $path, $prefix = null) {
        $files = $path->read();

        foreach ($files as $file) {
            $name = $file->getName();
            if (array_key_exists($name, $this->exclude)) {
                continue;
            }

            $name = $prefix . $name;
            if (array_key_exists($name, $this->exclude)) {
                continue;
            }

            if ($file->isDirectory()) {
                $this->indexDirectory($file, $name . File::DIRECTORY_SEPARATOR);
            } else {
                $this->indexFile($file, $name);
            }
        }
    }

    /**
     * Add a file to the index
     * @param zibo\library\filesystem\File $path
     * @param string $name
     * @return null
     */
    private function indexFile(File $path, $name) {
        if (!isset($this->index[$name])) {
            $this->index[$name] = array();
        }

        $this->index[$name][] = $path->getPath();
    }

    /**
     * Generates a PHP source file for the index
     * @param array $index
     * @return string
     */
    protected function generatePhp(array $index) {
        $output = "<?php\n\n";
        $output .= "/*\n";
        $output .= " * This file is generated by zibo\\core\\environment\\filebrowser\\IndexedFileBrowser.\n";
        $output .= " */\n";
        $output .= "\n";
        $output .= '$index = ' . var_export($index, true) . ';';

        return $output;
    }

}