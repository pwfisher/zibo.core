<?php

namespace zibo\core\deploy;

/**
 * Interface for remote console support
 */
interface RemoteConsole {

    /**
     * Executes a command on the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $command
     * @return string
     */
    public function executeRemoteCommand(Deployer $deployer, $command);

    /**
     * Executes a Zibo console command on the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $command
     * @return string
     */
    public function executeRemoteConsoleCommand(Deployer $deployer, $command);

}