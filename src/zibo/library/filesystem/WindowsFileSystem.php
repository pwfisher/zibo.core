<?php

namespace zibo\library\filesystem;

use zibo\library\String;

/**
 * Filesystem implementation for Windows filesystems
 */
class WindowsFileSystem extends FileSystem {

    /**
     * Check whether a path is a root path
     * @return boolean
     */
    public function isRootPath($path) {
        $drive = $this->getDrivePrefix($path);

        if ($drive && $drive == $path) {
            return true;
        }

        return false;
    }

    /**
     * Check whether a has an absolute path
     * @param File $file
     * @return boolean true when the file has an absolute path
     */
    public function isAbsolute(File $file) {
        $path = $file->getPath();

        if ($file->hasPharProtocol()) {
            $path = substr($path, 7);
        }

        if (String::startsWith($path, File::DIRECTORY_SEPARATOR) || $this->getDrivePrefix($path)) {
            return true;
        }

        return false;
    }

    /**
     * Get the absolute path for a file
     * @param File $file
     * @return string
     */
    public function getAbsolutePath(File $file) {
        $path = $file->getPath();

        $phar = $file->isInPhar();
        if ($phar) {
            $pharFile = new File(substr($path, strlen($phar->getPath()) + 7));
            $path = $phar->getPath() . File::DIRECTORY_SEPARATOR . $pharFile->getPath();
        }

        $hasPharProtocol = $file->hasPharProtocol($path);
        if ($hasPharProtocol) {
            $path = substr($path, 7);
        }

        if (!$this->isAbsolute($file)) {
            $path = str_replace('\\', File::DIRECTORY_SEPARATOR, getcwd()) . File::DIRECTORY_SEPARATOR . $path;
        }

        $drivePrefix = $this->getDrivePrefix($path);
        $path = substr($path, strlen($drivePrefix));

        $parts = explode(File::DIRECTORY_SEPARATOR, $path);
        $absolutePath = array();
        foreach ($parts as $part) {
            if ($part == '' || $part == '.') {
                continue;
            }
            if ($part == '..') {
                array_pop($absolutePath);
                continue;
            }
            array_push($absolutePath, $part);
        }

        $absolutePath = $drivePrefix . implode(File::DIRECTORY_SEPARATOR, $absolutePath);

        if ($phar || $hasPharProtocol) {
            $file = new File($absolutePath);
            $absolutePath = $file->getPath();
            if ($hasPharProtocol && !$file->hasPharProtocol() && $file->isPhar()) {
                $absolutePath = 'phar://' . $absolutePath;
            }
        }

        return $absolutePath;
    }

    /**
     * Get the parent of the provided file
     *
     * If you provide a path like /var/www/yoursite, the parent will be /var/www
     * @param File $file
     * @return File the parent of the file
     */
    public function getParent(File $file) {
        $path = $file->getPath();

        if (strpos($path, File::DIRECTORY_SEPARATOR) === false) {
            $parent = new File('.');
            return new File($this->getAbsolutePath($parent));
        }

        $name = $file->getName();
        $nameLength = strlen($name);
        $parent = substr($path, 0, ($nameLength + 1) * -1);
        if (!$parent) {
            return new File(File::DIRECTORY_SEPARATOR);
        }

        if (strpos($parent, File::DIRECTORY_SEPARATOR) === false) {
            $driveParent = $parent . File::DIRECTORY_SEPARATOR;
            $drivePrefix = $this->getDrivePrefix($driveParent);
            if ($drivePrefix == $driveParent) {
                return new File($drivePrefix);
            }
        }

        return new File($parent);
    }

    /**
     * Gets the drive prefix of a path (/, C:/)
     * @param string $path
     * @return boolean|string the drive prefix if an absolute path, false otherwise
     */
    private function getDrivePrefix($path) {
        if (!$path) {
            return false;
        }

        $pathLength = strlen($path);

        if ($pathLength == 1) {
            if ($path == File::DIRECTORY_SEPARATOR) {
                return File::DIRECTORY_SEPARATOR;
            }

            return false;
        }

        if ($path{0} == File::DIRECTORY_SEPARATOR) {
            if ($pathLength >= 3 && $this->isDrive($path{1}) && $path{2} == File::DIRECTORY_SEPARATOR) {
                return substr($path, 0, 3);
            }
            return File::DIRECTORY_SEPARATOR;
        }

        $drive = $path{0};
        if ($this->isDrive($drive) && String::startsWith($path, $drive . ':/')) {
            return $drive . ':/';
        }

        return false;
    }

    /**
     * Checks if a character is a drive letter
     * @param string $character
     * @return boolean true if the character is a drive letter, false otherwise
     */
    private function isDrive($character) {
        if ((ord($character) >= ord('A')) && (ord($character) <= ord('Z'))) {
            return true;
        }

        return false;
    }

}