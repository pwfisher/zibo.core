<?php

namespace zibo\test;

use \Exception;
use \PHPUnit_Extensions_SeleniumTestCase;

abstract class BaseSeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase {

    protected function runByRootUserSkipsTest() {
        $runAsRoot = false;
        if (isset($_ENV['APACHE_RUN_USER']) && $_ENV['APACHE_RUN_USER'] == 'root') {
            $runAsRoot = true;
        }
        if (isset($_ENV['USER']) && $_ENV['USER'] == 'root') {
            $runAsRoot = true;
        }

        if ($runAsRoot) {
            $this->markTestSkipped('Wrong expected results when run by root');
        }

        return false;
    }

    /**
     * Initializes the Zibo application from a directory
     *
     * This copies the data and config directories from a given directory into the Zibo application folder
     *
     * @param string $dir
     */
    protected function setUpApplication($dir) {
        $ziboRoot = getcwd();
        foreach ($this->applicationDirs() as $subDir) {
            $source = "$dir/$subDir";

            if (!is_dir($source)) {
                continue;
            }

            $destination = "$ziboRoot/application/$subDir";

            if (!file_exists($destination)) {
                mkdir($destination);
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Recursively copies directories and files
     *
     * @param string $source
     * @param string $destination
     */
    private function copy($source, $destination ) {
        if (!file_exists($source)) {
            throw new Exception("Unable to copy, the given source $source does not exist");
        }

        if (is_dir($source)) {
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            $directory = dir($source);
            while (false !== ($child = $directory->read())) {
                if (in_array($child, array('.', '..', '.svn'))) {
                    continue;
                }

                $this->copy($source . '/' . $child, $destination . '/' . $child);
            }

            $directory->close();
        } else {
            copy($source, $destination);
        }
    }

    /**
     * Application dirs to take into account for setting up or clearing the application environment
     */
    protected function applicationDirs() {
        return array('data', 'config', 'l10n');
    }

    private function delete($dir) {
        if (!file_exists($dir)) {
            return true;
        } else if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->delete($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * Tears down the Zibo application directory
     */
    protected function tearDownApplication() {
        $ziboRoot = getcwd();
        foreach ($this->applicationDirs() as $subDir) {
            $applicationSubDir = "$ziboRoot/application/$subDir";
            if (is_dir($applicationSubDir)) {
                $this->delete($applicationSubDir);
            }
        }
    }

}