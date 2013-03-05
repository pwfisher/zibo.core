<?php

namespace zibo\core\deploy\type;

use zibo\core\deploy\Deployer;

/**
 * Interface for the copy and sync implementation when deploying the system
 */
interface DeployType {

    /**
     * Copies the provided source to the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $source Path of the source file on the local server
     * @param string $destination Path of the destination on the remote server
     * @return null
     */
    public function copyFile(Deployer $deployer, $source, $destination);

    /**
     * Syncs the files of the provided source to the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $source Path of the source files on the local server
     * @param string $destination Path of the destination on the remote server
     * @return null
     */
    public function syncFiles(Deployer $deployer, $source, $destination);

    /**
     * Clears the cache of the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @return null
     */
    public function clearCache(Deployer $deployer);

}