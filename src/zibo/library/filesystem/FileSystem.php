<?php

namespace zibo\library\filesystem;

use zibo\library\filesystem\exception\FileSystemException;

/**
 * Default filesystem implementation
 */
abstract class FileSystem {

    /**
     * The instance of the FileSystem object
     * @var zibo\library\filesystem\FileSystem
     */
    protected static $instance;

    /**
     * Array containing file names which are ignored when reading a directory
     * @var array
     */
    protected $ignoredFileNames = array('.', '..', '.svn', '.DS_Store');

    /**
     * Get the instance of FileSystem
     * @return zibo\library\filesystem\FileSystem
     * @throws zibo\library\filesystem\exception\FileSystemException when the file system is not supported
     */
    public static function getInstance() {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $osType = strtoupper(PHP_OS);
        switch ($osType) {
            case 'LINUX':
            case 'UNIX':
            case 'DARWIN':
                self::$instance = new UnixFileSystem();

                break;
            case 'WIN32':
            case 'WINNT':
                self::$instance = new WindowsFileSystem();

                break;
            default:
                throw new FileSystemException('File system of ' . $osType . ' is not supported by Zibo');

                break;
        }

        return self::$instance;
    }

    /**
     * Get the names of files which are ignored when reading a directory
     * @return array $ignoredFileNames array with files which are ignored
     */
    public function getIgnoredFileNames() {
        return $this->ignoredFileNames;
    }

    /**
     * Set the names of files which are ignored when reading a directory
     * @param array $ignoredFileNames array with files which are ignored when reading a directory
     * @return null;
     */
    public function setIgnoredFileNames(array $ignoredFileNames) {
        $this->ignoredFileNames = $ignoredFileNames;
    }

    /**
     * Get the absolute path for a file
     * @param File $file
     * @return string
     */
    abstract public function getAbsolutePath(File $file);

    /**
     * Check whether a file has an absolute path
     * @param File $file
     * @return boolean true when the file has an absolute path
     */
    abstract public function isAbsolute(File $file);

    /**
     * Check whether a path is a root path (/, c:/, //server)
     * @param string $path
     * @return boolean true when the file is a root path, false otherwise
     */
    abstract public function isRootPath($path);

    /**
     * Get the parent of the provided file
     *
     * If you provide a path like /var/www/yoursite, the parent will be /var/www
     * @param File $file
     * @return File the parent of the file
     */
    abstract public function getParent(File $file);

    /**
     * Checks whether a file exists
     * @param File $file
     * @return boolean true when the file exists, false otherwise
     */
    public function exists(File $file) {
        $path = $this->getAbsolutePath($file);
        clearstatcache();
        return @file_exists($path);
    }

    /**
     * Checks whether a file is a directory
     * @param File $file
     * @return boolean true when the file is a directory, false otherwise
     */
    public function isDirectory(File $file) {
        if (!$this->exists($file)) {
            throw new FileSystemException($file->getAbsolutePath() . ' does not exist');
        }

        $path = $this->getAbsolutePath($file);

        return @is_dir($path);
    }

    /**
     * Checks whether a file is readable
     * @param File $file
     * @return boolean true when the file is readable, false otherwise
     */
    public function isReadable(File $file) {
        if (!$this->exists($file)) {
            throw new FileSystemException($file->getAbsolutePath() . ' does not exist');
        }

        $path = $this->getAbsolutePath($file);

        return @is_readable($path);
    }

    /**
     * Checks whether a file is writable.
     *
     * When the file exists, the file itself will be checked. If not, the parent directory will be checked
     * @param File $file
     * @return boolean true when the file is writable, false otherwise
     */
    public function isWritable(File $file) {
        if ($this->exists($file)) {
            $path = $this->getAbsolutePath($file);
            return @is_writable($path);
        } else {
            return $this->isWritable($file->getParent());
        }
    }

    /**
     * Get the timestamp of the last write to the file
     * @param File $file
     * @return int timestamp of the last modification
     * @throws zibo\library\filesystem\exception\FileSystemException when the file does not exist
     * @throws zibo\library\filesystem\exception\FileSystemException when the modification time could not be read
     */
    function getModificationTime(File $file) {
        if (!$this->exists($file)) {
            throw new FileSystemException($file->getAbsolutePath() . ' does not exist');
        }

        $path = $this->getAbsolutePath($file);
        $time = @filemtime($path);
        if ($time === false) {
            throw new FileSystemException('Cannot get the modification time of ' . $path);
        }

        return $time;
    }

    /**
     * Get the size of a file
     * @param File $file
     * @return int size of the file in bytes
     * @throws zibo\library\filesystem\exception\FileSystemException when the file is a directory
     * @throws zibo\library\filesystem\exception\FileSystemException when the file size could not be read
     */
    public function getSize(File $file) {
        if ($this->isDirectory($file)) {
            throw new FileSystemException($file->getAbsolutePath() . ' is a directory');
        }

        $path = $this->getAbsolutePath($file);
        $size = @filesize($path);
        if ($size === false) {
            throw new FileSystemException('Cannot get the filesize of ' . $path);
        }

        return $size;
    }

    /**
     * Get the permissions of a file or directory
     * @param File $file
     * @return int an octal value of the permissions. eg. 0755
     * @throws zibo\library\filesystem\exception\FileSystemException when the file or directory does not exist
     * @throws zibo\library\filesystem\exception\FileSystemException when the permissions could not be read
     */
    public function getPermissions(File $file) {
        if (!$this->exists($file)) {
            throw new FileSystemException($file->getAbsolutePath() . ' does not exist');
        }

        $path = $this->getAbsolutePath($file);
        $mode = @fileperms($path);
        if ($mode === false) {
            throw new FileSystemException('Could not get the permissions of ' . $path);
        }

        // fileperms() returns other bits in addition to the permission bits, like SUID, SGID and sticky bits
        // we only want the permission bits
        $permissions = $mode & 0777;

        return $permissions;
    }

    /**
     * Set the permissions of a file or directory
     * @param File $file
     * @param int $permissions an octal value of the permissions, so strings (such as "g+w") will not work properly. To ensure the expected operation, you need to prefix mode with a zero (0). eg. 0755
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the file or directory does not exist
     * @throws zibo\library\filesystem\exception\FileSystemException when the permissions could not be set
     */
    public function setPermissions(File $file, $permissions) {
        if (!$this->exists($file)) {
            throw new FileSystemException($file->getAbsolutePath() . ' does not exist');
        }

        $path = $this->getAbsolutePath($file);
        $result = @chmod($path, $permissions);
        if ($result === false) {
            throw new FileSystemException('Could not set the permissions of ' . $path . ' to ' . $permissions);
        }
    }

    /**
     * Read a file or directory
     * @param File $file file or directory to read
     * @return string|array when reading a file, a string with the content of the file will be returned. When reading a directory, an array will be returned containing File objects as value and the paths as key.
     * @throws zibo\library\filesystem\exception\FileSystemException when the file or directory could not be read
     */
    public function read(File $file, $recursive = false) {
        if ($this->isDirectory($file) || $file->isPhar()) {
            return $this->readDirectory($file, $recursive);
        } else {
            return $this->readFile($file);
        }
    }

    /**
     * Read a file
     * @param File $file file to read
     * @return string the content of the file
     * @throws zibo\library\filesystem\exception\FileSystemException when the file could not be read
     */
    private function readFile(File $file) {
        $path = $this->getAbsolutePath($file);

        $contents = file_get_contents($path);
        if ($contents === false) {
            $error = error_get_last();
            throw new FileSystemException('Could not read ' . $path . ': ' . $error['message']);
        }

        return $contents;
    }

    /**
     * Read a directory
     * @param File $dir directory to read
     * @param boolean $recursive true to read the subdirectories, false (default) to only read the given directory
     * @return array Array with a File object as value and it's path as key
     * @throws zibo\library\filesystem\exception\FileSystemException when the directory could not be read
     */
    private function readDirectory(File $dir, $recursive = false) {
        $path = $this->getAbsolutePath($dir);

        if ($dir->isPhar() && !$dir->hasPharProtocol($path)) {
            $path = 'phar://' . $path;
        }

        if (!($handle = @opendir($path))) {
            throw new FileSystemException('Could not read ' . $path);
        }

        $files = array();

        while (($f = readdir($handle)) !== false) {
            if (in_array($f, $this->ignoredFileNames)) {
                continue;
            }

            $file = new File($dir, $f);
            $files[$file->getPath()] = $file;

            if ($recursive && $file->isDirectory()) {
                $tmp = $this->readDirectory($file, true);
                foreach ($tmp as $k => $v) {
                    $files[$k] = $v;
                }
            }
        }

        return $files;
    }

    /**
     * Write content to a file
     * @param File $file file to write
     * @param string $content new content for the file
     * @param boolean $append true to append to file, false (default) to overwrite the file
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the file could not be written
     */
    public function write(File $file, $content = '', $append = false) {
        $path = $this->getAbsolutePath($file);
        if ($append) {
            $stat = @file_put_contents($path, $content, FILE_APPEND);
        } else {
            $stat = @file_put_contents($path, $content);
        }

        if ($stat === false) {
            $error = error_get_last();
            throw new FileSystemException('Could not write ' . $path . ': ' . $error['message']);
        }
    }

    /**
     * Create a directory
     * @param File $dir
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the directory could not be created
     */
    public function create(File $dir) {
        if ($this->exists($dir)) {
            return;
        }

        $path = $this->getAbsolutePath($dir);
        $result = @mkdir($path, 0755, true);
        if ($result === false) {
            $error = error_get_last();
            throw new FileSystemException('Could not create ' . $path . ': ' . $error['message']);
        }
    }

    /**
     * Delete a file or directory
     * @param File $file
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the file or directory could not be deleted
     */
    public function delete(File $file) {
        if ($this->isDirectory($file)) {
            $this->deleteDirectory($file);
        } else {
            $this->deleteFile($file);
        }
    }

    /**
     * Delete a file
     * @param File $file
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the file could not be deleted
     */
    protected function deleteFile(File $file) {
        $path = $file->getAbsolutePath();
        $result = @unlink($path);
        if (!$result) {
            $error = error_get_last();
            throw new FileSystemException('Could not delete ' . $path . ': ' . $error['message']);
        }
    }

    /**
     * Delete a directory
     * @param File $dir
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the dir could not be read or deleted
     */
    protected function deleteDirectory(File $dir) {
        $path = $dir->getAbsolutePath();
        if (!($handle = @opendir($path))) {
            throw new FileSystemException('Could not read ' . $path);
        }

        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $this->delete(new File($dir, $file));
            }
        }

        closedir($handle);

        $result = @rmdir($path);
        if (!$result) {
            $error = error_get_last();
            throw new FileSystemException('Could not delete ' . $path . ': ' . $error['message']);
        }
    }

    /**
     * Locks the provided file, locks have to be checked manually
     * @param File $file The file to lock
     * @param boolean $waitForLock True to keep trying to get the lock, false to throw an exception when the file is locked
     * @param integer $waitTime Time in microseconds to wait between the lock checks
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when $waitForLock is false and the file is locked
     */
    public function lock(File $file, $waitForLock = true, $waitTime = 10000) {
        $lockFile = $file->getLockFile();

        $parent = $lockFile->getParent();
        $this->create($parent);

        if (!$waitForLock) {
            if ($this->exists($lockFile)) {
                throw new FileSystemException('Could not lock ' . $file->getPath() . ': The file is already locked');
            }
        } else {
            $this->waitForUnlock($file, $waitTime);
        }

        $this->write($lockFile, $file->getPath());
    }

    /**
     * Unlocks the provided file
     * @param File $file File to unlock
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the file is not locked
     */
    public function unlock(File $file) {
        $lockFile = $file->getLockFile();

        if (!$this->exists($lockFile)) {
            throw new FileSystemException('Could not unlock ' . $file->getPath() . ': The file is not locked');
        }

        $this->delete($lockFile);
    }

    /**
     * Checks whether the provided file is locked
     * @param File $file The file to check
     * @return boolean True if the provided file is locked, false otherwise
     */
    public function isLocked(File $file) {
        $lockFile = $file->getLockFile();
        return $this->exists($lockFile);
    }

    /**
     * Wait until the provided file is unlocked
     * @param File $file The locked file to wait for
     * @param integer $waitTime Time in microseconds to wait between the lock checks
     * @return null
     */
    public function waitForUnlock(File $file, $waitTime = 10000) {
        $lockFile = $file->getLockFile();

        $isFileLocked = true;

        do {
            if ($this->exists($lockFile)) {
                usleep($waitTime);
                continue;
            }

            $isFileLocked = false;
        } while ($isFileLocked);
    }

    /**
     * Copy a file or directory to another destination
     * @param File $source
     * @param File $destination
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the source could not be copied
     */
    public function copy(File $source, File $destination) {
        if ($this->isDirectory($source)) {
            $this->copyDirectory($source, $destination);
        } else {
            $this->copyFile($source, $destination);
        }
    }

    /**
     * Copy a file to another file
     * @param File $source
     * @param File $destination
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the source could not be copied
     */
    private function copyFile(File $source, File $destination) {
        $destinationParent = $destination->getParent();
        $this->create($destinationParent);

        $sourcePath = $source->getAbsolutePath();
        $destinationPath = $destination->getAbsolutePath();

        if ($sourcePath == $destinationPath) {
            return;
        }

        $result = @copy($sourcePath, $destinationPath);
        if (!$result) {
            $error = error_get_last();
            throw new FileSystemException('Could not copy ' . $sourcePath . ' to ' . $destinationPath . ': ' . $error['message']);
        }
        $this->setPermissions($destination, 0644); // $source->getPermissions());
    }

    /**
     * Copy a directory to another directory
     * @param File $source
     * @param File $destination
     * @return null
     * @throws zibo\library\filesystem\exception\FileSystemException when the source could not be read
     */
    private function copyDirectory(File $source, File $destination) {
        $sourcePath = $source->getAbsolutePath();
        if (!($handle = opendir($sourcePath))) {
            throw new FileSystemException('Could not read ' . $sourcePath);
        }

        $files = false;
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $files = true;
                $this->copy(new File($source, $file), new File($destination, $file));
            }
        }
        if (!$files) {
            // since copying an empty directory does nothing, we create the destination
            $this->create($destination);
        }
    }

    /**
     * Move a file to another directory
     * @param File $source source file for the move
     * @param File $destination new destination for the source file
     * @return null
     */
    public function move(File $source, File $destination) {
        if ($this->isDirectory($source)) {
            $this->copyDirectory($source, $destination);
            $this->delete($source);
        } else {
            $this->copyFile($source, $destination);
            $this->delete($source);
        }
    }

}