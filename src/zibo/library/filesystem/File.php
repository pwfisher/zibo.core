<?php

namespace zibo\library\filesystem;

use zibo\library\filesystem\exception\FileSystemException;

/**
 * File data object, facade for the filesystem library
 */
class File {

    /**
     * Suffix for a lock file
     * @var string
     */
    const LOCK_SUFFIX = '.lock';

    /**
     * Directory separator
     * @var string
     */
    const DIRECTORY_SEPARATOR = '/';

    /**
     * Path of this file object
     * @var string
     */
    private $path;

    /**
     * Flag to see if this path is a root path
     * @var boolean
     */
    private $isRootPath;

    /**
     * The lock file of this file
     * @var File
     */
    private $lockFile;

    /**
     * Construct a file object
     * @param string|File $path
     * @param string|File $child file in the provided path (optional)
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * path is empty
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * child is absolute
     */
    public function __construct($path, $child = null) {
        $this->isRootPath = false;
        $this->path = self::retrievePath($path, $this->isRootPath);

        if ($child != null) {
            $child = new File($child);
            if ($child->isAbsolute()) {
                throw new FileSystemException('Child ' . $child->getPath() . ' cannot be absolute');
            }

            $childPath = $child->getPath();
            if ($child->hasPharProtocol()) {
                $childPath = substr($childPath, 7);
            }

            if (!$this->isRootPath) {
                $this->path .= self::DIRECTORY_SEPARATOR;
            }

            $this->path .= $childPath;
        }

        if ($this->isInPhar() && !$this->hasPharProtocol()) {
            $this->path = 'phar://' . $this->path;
        }
    }

    /**
     * Get a string representation of this file
     * @return string the path of the file
     */
    public function __toString() {
        return $this->path;
    }

    /**
     * Retrieve the path from a string or File object
     * @param string|File $path
     * @return string the path in a string format
     */
    private static function retrievePath($path, &$isRootPath) {
        if ($path instanceof self) {
            $path = $path->getPath();
        }

        if (!is_string($path) || !$path) {
            throw new FileSystemException('Cannot create a file with an invalid or empty path');
        }

        $path = str_replace('\\', self::DIRECTORY_SEPARATOR, $path);

        $isRootPath = FileSystem::getInstance()->isRootPath($path);
        if (!$isRootPath) {
            $path = rtrim($path, self::DIRECTORY_SEPARATOR);
        }

        return $path;
    }

    /**
     * Creates a temporary file
     * @param string $name Prefix for the name
     * @return zibo\library\filesystem\File
     */
    public static function getTemporaryFile($name = 'temp') {
        return new File(tempnam(sys_get_temp_dir(), $name));
    }

    /**
     * Get the name of the file
     *
     * If you provide a path like /var/www/yoursite, the name will be yoursite
     * @return string
     */
    public function getName() {
        $lastSeparator = strrpos($this->path, self::DIRECTORY_SEPARATOR);
        if ($lastSeparator === false) {
            return $this->path;
        }

        return substr($this->path, $lastSeparator + 1);
    }

    /**
     * Get the parent of the file
     *
     * If you provide a path like /var/www/yoursite, the parent will be /var/www
     * @return File the parent of the file
     */
    public function getParent() {
        return FileSystem::getInstance()->getParent($this);
    }

    /**
     * Get the extension of the file
     * @return string if the file has an extension, you got it, else an empty string
     */
    public function getExtension() {
        $name = $this->getName();
        $extensionSeparator = strrpos($name, '.');
        if ($extensionSeparator === false) {
            return '';
        }

        return strtolower(substr($name, $extensionSeparator + 1));
    }

    /**
     * Check if the file has one of the provided extensions
     * @param string|array $extension an extension as a string or an array of extensions
     * @return true if the file has the provided extension, or one of if the $extension var is an array
     */
    public function hasExtension($extension) {
        if (!is_array($extension)) {
            $extension = array($extension);
        }
        return in_array($this->getExtension(), $extension);
    }

    /**
     * Get the path of this file
     * @return string
     */
    public function getPath() {
       return $this->path;
    }

    /**
     * Get a safe file name for a copy in order to not overwrite existing files
     *
     * When you are trying to copy a file document.txt to /tmp and your /tmp contains document.txt,
     * the copy file will be /tmp/document-1.txt. If this file also exists, it will be /tmp/document-2.txt and on and on...
     * @return File a file object containing a safe file name for a copy
     */
    public function getCopyFile() {
        if (!$this->exists()) {
            return $this;
        }

        $baseName = $this->getName();
        $parent = $this->getParent();
        $extension = $this->getExtension();
        if ($extension != '') {
            $baseName = substr($baseName, 0, (strlen($extension) + 1) * -1);
            $extension = '.' . $extension;
        }

        $index = 0;
        do {
            $index++;
            $copyFile = new File($parent, $baseName . '-' . $index . $extension);
        } while ($copyFile->exists());

        return $copyFile;
    }

    /**
     * Get the absolute path of your file
     * @return string
     */
    public function getAbsolutePath() {
        return FileSystem::getInstance()->getAbsolutePath($this);
    }

    /**
     * Check whether this file has an absolute path
     * @return boolean true if the file has an absolute path, false if not
     */
    public function isAbsolute() {
        return FileSystem::getInstance()->isAbsolute($this);
    }

    /**
     * Check whether this file is a root path (/, c:/, //server)
     * @return boolean true if the file is a root path, false if not
     */
    public function isRootPath() {
        return $this->isRootPath;
    }

    /**
     * Checks if the file exists
     * @return boolean true if the file exists, false if not
     */
    public function exists() {
        return FileSystem::getInstance()->exists($this);
    }

    /**
     * Checks if the file is a directory
     * @return boolean true if the file is a directory, false if not
     */
    public function isDirectory() {
        return FileSystem::getInstance()->isDirectory($this);
    }

    /**
     * Checks if the file is readable
     * @return boolean true if the file is readable, false if not
     */
    public function isReadable() {
        return FileSystem::getInstance()->isReadable($this);
    }

    /**
     * Checks if the file is writable
     * @return boolean true if the file is writable, false if not
     */
    public function isWritable() {
        return FileSystem::getInstance()->isWritable($this);
    }

    /**
     * Checks if the file is a phar, based on the extension of the file
     * @return boolean true if the file is a phar, false if not
     */
    public function isPhar() {
        return $this->getExtension() == 'phar';
    }

    /**
     * Checks if the file is in a phar, checks the path for .phar/
     * @return boolean true if the file is in a phar, false if not
     */
    public function isInPhar() {
        $match = null;

        $positionPhar = strpos($this->path, '.phar' . self::DIRECTORY_SEPARATOR);
        if ($positionPhar === false) {
            return false;
        }

        $phar = substr($this->path, 0, $positionPhar + 5);

        if ($this->hasPharProtocol($phar)) {
            $phar = substr($phar, 7);
        }

        return new File($phar);
    }

    /**
     * Checks if a path has been prefixed with the phar protocol (phar://)
     * @param string $path if none provided, the path of the file is assumed
     * @return boolean true if the protocol is prefixed, false otherwise
     */
    public function hasPharProtocol($path = null) {
        if ($path == null) {
            $path = $this->path;
        }

        return strncmp($path, 'phar://', 7) == 0;
    }

    /**
     * Get the time the file was last modified
     * @return int timestamp of the modification time
     */
    public function getModificationTime() {
        return FileSystem::getInstance()->getModificationTime($this);
    }

    /**
     * Get the size of a file
     * @return int size of a file in bytes
     */
    public function getSize() {
        return FileSystem::getInstance()->getSize($this);
    }

    /**
     * Get the permissions of a file
     * @return int an octal value of the permissions. eg. 0755
     */
    public function getPermissions() {
        return FileSystem::getInstance()->getPermissions($this);
    }

    /**
     * Set the permissions of a file
     * @param int an octal value of the permissions. eg. 0755
     */
    public function setPermissions($permissions) {
        return FileSystem::getInstance()->setPermissions($this, $permissions);
    }

    /**
     * Read the file or directory
     * @param boolean $recursive When reading a directory: true to read subdirectories, false to read only the direct children
     * @return string|array if the file is not a directory, the contents of the file will be returned
     *          in a string, else the files in the directory will be returned in an array
     * @throws zibo\library\filesystem\exception\FileSystemException when the file or directory could not be read
     */
    public function read($recursive = false) {
        return FileSystem::getInstance()->read($this, $recursive);
    }

    /**
     * Creates or updates this file
     * @param string $content The content to write
     * @param boolean $append Set to true to append to the file, false to
     * overwrite (default)
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * file could not be written
     */
    public function write($content = '', $append = false) {
        FileSystem::getInstance()->write($this, $content, $append);
    }

    /**
     * Creates a new directory
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * directory could not be created
     */
    public function create() {
        FileSystem::getInstance()->create($this);
    }

    /**
     * Deletes this file or directory
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * file or directory could not be deleted
     */
    public function delete() {
        FileSystem::getInstance()->delete($this);
    }

    /**
     * Gets the lock file of this file
     * @return File The lock file of this file
     */
    public function getLockFile() {
        if (!$this->lockFile) {
            $this->lockFile = new File($this->path . self::LOCK_SUFFIX);
        }

        return $this->lockFile;
    }

    /**
     * Locks this file, locks have to be checked manually
     * @param boolean $waitForLock True to keep trying to get the lock, false to throw
     * an exception when the file is locked
     * @param integer $waitTime Time in microseconds to wait between the lock checks
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when $waitForLock
     * is false and the file is locked
     */
    public function lock($waitForLock = true, $waitTime = 10000) {
        FileSystem::getInstance()->lock($this, $waitForLock, $waitTime);
    }

    /**
     * Unlocks this file
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * file is not locked
     */
    public function unlock() {
        FileSystem::getInstance()->unlock($this);
    }

    /**
     * Checks whether this file is locked
     * @return boolean True if the file is locked, false otherwise
     */
    public function isLocked() {
        return FileSystem::getInstance()->isLocked($this);
    }

    /**
     * Wait until this file is unlocked
     * @param integer $waitTime Time in microseconds to wait between the lock
     * checks
     * @return null
     */
    public function waitForUnlock($waitTime = 10000) {
        FileSystem::getInstance()->waitForUnlock($this, $waitTime);
    }

    /**
     * Copy this file
     * @param File $destination
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * file could not be copied
     */
    public function copy(File $destination) {
        FileSystem::getInstance()->copy($this, $destination);
    }

    /**
     * Move this file
     * @param File $destination
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * file could not be moved
     */
    public function move(File $destination) {
        FileSystem::getInstance()->move($this, $destination);
    }

    /**
     * Pass the file through to the output
     * @param resource $output Resource handle to write the file to
     * @return null
     */
    public function passthru($output = null) {
        if (!$output) {
            while (ob_get_level() !== 0) {
                ob_end_clean();
            }
        }

        if (!$this->isReadable()  || (!$output && connection_status() != 0)) {
            return false;
        }

        set_time_limit(0);

        if ($input = fopen($this->getAbsolutePath(), 'rb')) {
            while (!feof($input) && (!$output && connection_status() == 0)) {
                if ($output) {
                    fwrite($output, fread($input, 1024*8));
                } else {
                    print(fread($input, 1024*8));
                    flush();
                }
            }
            fclose($input);
        } else {
            throw new FileSystemException('Could not open the input file');
        }

        if ($output) {
            return true;
        }

        return connection_status() == 0 && !connection_aborted();
    }

}