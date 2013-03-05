<?php

namespace zibo\core\deploy\type;

use zibo\core\deploy\Deployer;
use zibo\core\deploy\DeployProfile;
use zibo\core\deploy\RemoteConsole;

use \Exception;

/**
 * Deployment over SSH with remote console support
 */
class SshDeployType implements DeployType, RemoteConsole {

    /**
     * Parameter name for the username on the remote server
     * @var string
     */
    const PARAM_USERNAME = 'username';

    /**
     * Parameter name of the path to the SSH key
     * @var string
     */
    const PARAM_SSH_KEY = 'ssh.key';

    /**
     * Constructs a new SSH file sync
     * @throws Exception
     */
    public function __construct() {
        $osType = strtoupper(PHP_OS);
        if ($osType != 'LINUX' && $osType != 'UNIX' && $osType != 'DARWIN') {
            throw new Exception('Could not create SshDeployType: a POSIX system is needed');
        }
    }

    /**
     * Copies the provided source to the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $source Path of the source file on the local server
     * @param string $destination Path of the destination on the remote server
     * @return null
     */
    public function copyFile(Deployer $deployer, $source, $destination) {
        $binary = $deployer->getZibo()->getParameter('system.binary.scp', 'scp');
        $profile = $deployer->getProfile();
        $sshKey = $this->getSshKey($profile);
        $server = $profile->getServer();
        $username = $profile->getParameter(self::PARAM_USERNAME);

        // -r recursive: recurse into directories
        $cmd = $binary . ' -r ';
        if ($sshKey) {
            $cmd .= '-i ' . $sshKey . ' ';
        }

        $cmd .= $source . ' ';

        if ($username) {
            $cmd .= $username . '@';
        }

        $cmd .= $server . ':' . $destination;

        $deployer->executeLocalCommand($cmd);
    }

    /**
     * Syncs the files of the provided source to the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $source Path of the source files on the local server
     * @param string $destination Path of the destination on the remote server
     * @return null
     */
    public function syncFiles(Deployer $deployer, $source, $destination) {
        $binary = $deployer->getZibo()->getParameter('system.binary.rsync', 'rsync');
        $profile = $deployer->getProfile();
        $sshKey = $this->getSshKey($profile);
        $server = $profile->getServer();
        $username = $profile->getParameter(self::PARAM_USERNAME);

        // -u update: skip files which are newer on the receiver
        // -r recursive: recurse into directories
        // -l links: copy symlinks as symlinks
        // -t times: preserve modification times
        // -p perms: preserve permissions
        $cmd = $binary . ' -urltp ';

        if ($sshKey) {
            $cmd .= '-e "ssh -i ' . $this->sshKey . '" ';
        }

        $cmd .= $source . ' ';

        if ($username) {
            $cmd .= $username . '@';
        }

        $cmd .= $server . ':' . $destination;

        $deployer->executeLocalCommand($cmd);
    }

    /**
     * Clears the cache of the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @return null
     */
    public function clearCache(Deployer $deployer) {
        $this->executeRemoteConsoleCommand($deployer, 'cache clear');
    }

    /**
     * Executes a command on the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $command
     * @return string
     */
    public function executeRemoteCommand(Deployer $deployer, $command) {
        $binary = $deployer->getZibo()->getParameter('system.binary.ssh', 'ssh');
        $profile = $deployer->getProfile();
        $sshKey = $this->getSshKey($profile);
        $server = $profile->getServer();
        $username = $profile->getParameter(self::PARAM_USERNAME);

        $cmd = $binary . ' ';

        if ($sshKey) {
            $cmd .= '-i ' . $sshKey . ' ';
        }

        if ($username) {
            $cmd .= $username . '@';
        }

        $cmd .= $server . ' "' . $command . '"';

        return $deployer->executeLocalCommand($cmd);
    }

    /**
     * Executes a Zibo console command on the remote server
     * @param zibo\core\deploy\Deployer $deployer Instance of the deployer
     * @param string $command
     * @return string
     */
    public function executeRemoteConsoleCommand(Deployer $deployer, $command) {
        $command = 'php ' . $deployer->getProfile()->getApplicationPath() . '/console.php ' . $command;

        return $this->executeRemoteCommand($deployer, $command);
    }

    /**
     * Gets the path to the SSH key from the deploy profile
     * @param zibo\core\deploy\DeployProfile $profile
     * @return string|null
     * @throws Exception when the provided path is invalid
     */
    protected function getSshKey(DeployProfile $profile) {
        $path = $profile->getParameter(self::PARAM_SSH_KEY);
        if (!$path) {
            return null;
        }

        $file = new File($path);
        if (!$file->exists() || $file->isDirectory() || !$file->isReadable()) {
            throw new Exception('Provided SSH key is not a valid path: ' . $path);
        }

        return $path;
    }

}