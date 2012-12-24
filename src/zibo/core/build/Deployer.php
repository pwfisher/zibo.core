<?php

namespace zibo\core\build;

use zibo\core\BootstrapConfig;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

use \Exception;

/**
 * Deployer for your Zibo installation to a remote server.
 *
 * This implementation is for POSIX environments and uses SSH and rsync to
 * perform the deploy actions.
 */
class Deployer {

    /**
     * Name of the event before deploying
     * @var string
     */
    const EVENT_PRE_DEPLOY = 'deploy.pre';

    /**
     * Name of the event after deploying
     * @var string
     */
    const EVENT_POST_DEPLOY = 'deploy.post';

    /**
     * Path of the application directory on the local server
     * @var string
     */
    protected $localApplication;

    /**
     * Path of the public directory on the local server
     * @var string
     */
    protected $localPublic;

    /**
     * Hostname of the server
     * @var string
     */
    protected $server;

    /**
     * Path of the application directory on the remote server
     * @var string
     */
    protected $remoteApplication;

    /**
     * Path of the public directory on the remote server
     * @var string
     */
    protected $remotePublic;

    /**
     * Path to the SSH key on the local server
     * @var string
     */
    protected $sshKey;

    /**
     * Custom parameters for the deploy hooks
     * @var array
     */
    protected $parameters;

    /**
     * Constructs a new Zibo deployer
     * @param string $server Hostname or IP address of the server, prefix
     * with username@ to specify the remote username
     * @param string $application Path of the application directory on the
     * remote server
     * @param string $public Path of the public directory on the remote server
     * @return null
     */
    public function __construct($server, $application, $public) {
        $this->localApplication = null;
        $this->localPublic = null;

        $this->setRemoteServer($server);
        $this->setRemoteApplicationDirectory($application);
        $this->setRemotePublicDirectory($public);

        $this->sshKey = null;
        $this->parameters = array();
    }

    /**
     * Sets the remote server
     * @param string $server Hostname or IP address of the server, prefix
     * with username@ to specify the remote username
     * @return null
     * @throws Exception when the provided server is invalid
     */
    protected function setRemoteServer($server) {
        if (!is_string($server) || $server == '' || strpos($server, ' ') !== false) {
            throw new Exception('Provided remote server is invalid: ' . $server);
        }

        $this->server = $server;
    }

    /**
     * Gets the remote server
     * @preturn string Hostname or IP address of the server, prefixed with
     * username@ to specify the remote username
     * @return null
     */
    public function getRemoteServer() {
        return $this->server;
    }

    /**
     * Sets the application directory on the remote server
     * @param string $path Path of the application directory
     * @return null
     * @throws Exception when the provided path is invalid
     */
    protected function setRemoteApplicationDirectory($path) {
        if (!is_string($path) || $path == '' || strpos($path, ' ') !== false) {
            throw new Exception('Provided remote application directory is invalid: ' . $path);
        }

        $this->remoteApplication = rtrim($path, '/');
    }

    /**
     * Gets the application directory on the remote server
     * @return string Path of the application directory
     */
    public function getRemoteApplicationDirectory() {
        return $this->remoteApplication;
    }

    /**
     * Sets the public directory on the remote server
     * @param string $path Path of the public directory
     * @return null
     * @throws Exception when the provided path is invalid
     */
    protected function setRemotePublicDirectory($path) {
        if (!is_string($path) || $path == '' || strpos($path, ' ') !== false) {
            throw new Exception('Provided remote public directory is invalid: ' . $path);
        }

        $this->remotePublic = rtrim($path, '/');
    }

    /**
     * Gets the public directory on the remote server
     * @return string Path of the public directory
     */
    public function getRemotePublicDirectory() {
        return $this->remotePublic;
    }

    /**
     * Sets the path to the SSH key for the remote server connection
     * @param string $path Path to the SSH key (id_rsa file)
     * @return null
     * @throws Exception when the provided path is invalid
     */
    public function setSshKey($path) {
        $file = new File($path);
        if (!$file->exists() || $file->isDirectory() || !$file->isReadable()) {
            throw new Exception('Provided SSH key is not a valid path: ' . $path);
        }

        $this->sshKey = $path;
    }

    /**
     * Gets the path to the SSH key
     * @return string Path to the SSH key
     */
    public function getSshKey() {
        return $this->sshKey;
    }

    /**
     * Sets a custom parameter for the deploy hooks
     * @param string $name Name of the parameter
     * @param mixed $value Value of the parameter
     * @return null
     */
    public function setParameter($name, $value) {
        $this->parameters[$name] = $value;
    }

    /**
     * Gets a custom parameter
     * @param string $name Name of the parameter
     * @param mixed $default Default value for when the parameter not set
     * @return mixed Parameter value if set, the provided default value
     * otherwise
     */
    public function getParameter($name, $default = null) {
        if (!isset($this->parameters[$name])) {
            return $default;
        }

        return $this->parameters[$name];
    }

    /**
     * Deploys the current Zibo installation to the set deployment parameters
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @param string $environment Name of the environment
     * @return null
     * @throws Exception when the current system or environment is not supported
     */
    public function deploy(Zibo $zibo, $environment = null) {
        $osType = strtoupper(PHP_OS);
        if ($osType != 'LINUX' && $osType != 'UNIX' && $osType != 'DARWIN') {
            throw new Exception('Could not deploy your system: POSIX system is needed for this command');
        }

        if ($zibo->getEnvironment()->getName() == 'dev') {
            throw new Exception('Could not deploy your system: run the build command first');
        }

        $this->localApplication = $zibo->getApplicationDirectory()->getAbsolutePath();
        $this->localPublic = $zibo->getPublicDirectory()->getAbsolutePath();

        // prepare the deployment
        $zibo->triggerEvent(self::EVENT_PRE_DEPLOY, $this);

        $this->executeLocalConsoleCommand('cache clear');
        $this->executeLocalConsoleCommand('session clean');

        // sync the files
        $this->syncFiles($this->localApplication . '/', $this->remoteApplication);
        $this->syncFiles($this->localPublic . '/', $this->remotePublic);

        // update the bootstrap
        $config = $this->remoteApplication . '/' . BootstrapConfig::SCRIPT_CONFIG;

        $this->updateBootstrap($config, $environment);

        // update the main scripts
        $this->updateScript(new File($this->localPublic, 'index.php'), $this->remotePublic . '/index.php', $config);
        $this->updateScript(new File($this->localApplication, 'console.php'), $this->remoteApplication. '/console.php', $config);

        // post deploy actions
        $this->executeRemoteConsoleCommand('cache clear');

        $zibo->triggerEvent(self::EVENT_POST_DEPLOY, $this);

        $this->localApplication = null;
        $this->localPublic = null;
    }

    /**
     * Updates the bootstrap config on the remote server
     * @param string $config Remote path to the config
     * @param string $environment Name of the deploy environment
     * @return null
     */
    protected function updateBootstrap($config, $environment) {
        $tempFile = File::getTemporaryFile('bootstrap');

        $bootstrap = new BootstrapConfig();
        $bootstrap->read(new File(ZIBO_CONFIG));
        $bootstrap->setCoreDirectory(new File($this->remoteApplication));
        $bootstrap->setApplicationDirectory(new File($this->remoteApplication));
        $bootstrap->setPublicDirectory(new File($this->remotePublic));
        $bootstrap->removeModulesDirectories();

        if ($environment) {
            $bootstrap->setEnvironment($environment);
        }

        $bootstrap->write($tempFile);

        $this->copyFiles($tempFile->getAbsolutePath(), $config);

        $tempFile->delete();
    }

    /**
     * Updates the provided script with the new config and syncs it with the
     * remote server
     * @param zibo\library\filesystem\File $source Source script
     * @param string $destination Path on the remote server
     * @param string $config Path to the new config
     * @return null
     */
    protected function updateScript(File $source, $destination, $config) {
        if (!$source->exists()) {
            return;
        }

        $tempFile = File::getTemporaryFile('script');
        $source->copy($tempFile);

        $bootstrap = new BootstrapConfig();
        $bootstrap->updateScript($tempFile, $config);

        $this->copyFiles($tempFile->getAbsolutePath(), $destination);

        $tempFile->delete();
    }

    /**
     * Copies the provided source to the remote server
     * @param string $source Path of the source files
     * @param string $destination Path of the destination
     * @return null
     */
    protected function copyFiles($source, $destination) {
        $command = 'scp -r ';
        if ($this->sshKey) {
            $command .= '-i ' . $this->sshKey . ' ';
        }

        $command .= $source . ' ' . $this->server . ':' . $destination;

        $this->executeLocalCommand($command);
    }

    /**
     * Rsyncs the provided source to the remote server
     * @param string $source Path of the source files
     * @param string $destination Path of the destination
     * @return null
     */
    protected function syncFiles($source, $destination) {
        $command = 'rsync -urltD ';
        if ($this->sshKey) {
            $command .= '-e "ssh -i ' . $this->sshKey . '" ';
        }

        $command .= $source . ' ' . $this->server . ':' . $destination;

        $this->executeLocalCommand($command);
    }

    /**
     * Executes a command on the local server
     * @param string $command
     * @return string
     */
    public function executeLocalCommand($command) {
        echo $command . "\n";

        return shell_exec($command);
    }

    /**
     * Executes a Zibo console command on the local server
     * @param string $command
     * @return string
     */
    public function executeLocalConsoleCommand($command) {
        if (!$this->localApplication) {
            throw new Exception('Could not execute local console command: only available while deploying');
        }

        $command = 'php ' . $this->localApplication . '/console.php ' . $command;

        return $this->executeLocalCommand($command);
    }

    /**
     * Executes a command on the remote server
     * @param string $command
     * @return string
     */
    public function executeRemoteCommand($command) {
        $cmd = 'ssh ';
        if ($this->sshKey) {
            $cmd .= '-i ' . $this->sshKey . ' ';
        }

        $cmd .= $this->server . ' "' . $command . '"';

        return $this->executeLocalCommand($cmd);
    }

    /**
     * Executes a Zibo console command on the remote server
     * @param string $command
     * @return string
     */
    public function executeRemoteConsoleCommand($command) {
        $command = 'php ' . $this->remoteApplication . '/console.php ' . $command;

        return $this->executeRemoteCommand($command);
    }

}