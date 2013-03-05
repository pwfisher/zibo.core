<?php

namespace zibo\core\deploy;

use zibo\core\console\output\Output;
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
     * Output for the deployer
     * @var zibo\core\console\output\Output
     */
    protected $output;

    /**
     * Instance of Zibo
     * @var zibo\core\Zibo
     */
    protected $zibo;

    /**
     * Deploy profile
     * @var DeployProfile
     */
    protected $profile;

    /**
     * Instance of the deploy implementation
     * @var zibo\core\deploy\DeployType
     */
    protected $type;

    /**
     * Path of the application directory on the local server
     * @var string
     */
    protected $pathApplication;

    /**
     * Path of the public directory on the local server
     * @var string
     */
    protected $pathPublic;

    /**
     * Sets the output implementation
     * @param zibo\core\console\output\Output $output Instance of the output
     * @return null
     */
    public function setOutput(Output $output) {
        $this->output = $output;
    }

    /**
     * Gets the output implementation
     * @return zibo\core\console\output\Output
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * Gets the instance of Zibo
     * @return zibo\core\Zibo
     */
    public function getZibo() {
        return $this->zibo;
    }

    /**
     * Gets the deploy profile
     * @return DeployProfile
     */
    public function getProfile() {
        return $this->profile;
    }

    /**
     * Gets the deploy type
     * @return DeployType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Deploys the current Zibo installation to the set deployment parameters
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @param string $environment Name of the environment
     * @return null
     * @throws Exception when the current system or environment is not supported
     */
    public function deploy(Zibo $zibo, DeployProfile $profile) {
        if ($zibo->getEnvironment()->getName() == 'dev') {
            throw new Exception('Could not deploy your system: run the build command first');
        }

        if ($this->output) {
            $this->output->write('Deploying from profile ' . $profile->getName() . ' to environment ' . $profile->getEnvironment() . ' through ' . $profile->getType());
        }

        $this->zibo = $zibo;
        $this->profile = $profile;
        $this->type = $zibo->getDependency('zibo\\core\\deploy\\type\\DeployType', $profile->getType());
        $this->pathApplication = $zibo->getApplicationDirectory()->getAbsolutePath();
        $this->pathPublic = $zibo->getPublicDirectory()->getAbsolutePath();

        // prepare the deployment
        $zibo->triggerEvent(self::EVENT_PRE_DEPLOY, $this);

        if ($this->output) {
            $this->output->write('Clearing cache and sessions on local installation');
        }

        $this->executeLocalConsoleCommand('session clean --force');
        $this->executeLocalConsoleCommand('cache clear');

        // sync the files
        if ($this->output) {
            $this->output->write('Synchronizing the files...');
        }

        $remotePathApplication = $this->profile->getApplicationPath();
        $remotePathPublic = $this->profile->getPublicPath();

        $this->type->syncFiles($this, $this->pathApplication, $remotePathApplication);
        $this->type->syncFiles($this, $this->pathPublic, $remotePathPublic);

        // update the bootstrap
        if ($this->output) {
            $this->output->write('Updating script paths on remote installation');
        }

        $config = $remotePathApplication . '/' . BootstrapConfig::SCRIPT_CONFIG;

        $this->updateBootstrap($config);

        // update the main scripts
        $this->updateScript(new File($this->pathPublic, 'index.php'), $remotePathPublic. '/index.php', 'updateScript', $config);
        $this->updateScript(new File($this->pathApplication, 'console.php'), $remotePathApplication . '/console.php', 'updateScript', $config);

        // update composer autoloader scripts
        $this->updateScript(new File($this->pathApplication, 'vendor/composer/autoload_classmap.php'), $remotePathApplication . '/vendor/composer/autoload_classmap.php', 'updateComposerScript', $remotePathApplication);
        $this->updateScript(new File($this->pathApplication, 'vendor/composer/autoload_namespaces.php'), $remotePathApplication . '/vendor/composer/autoload_namespaces.php', 'updateComposerScript', $remotePathApplication);

        // post deploy actions
        if ($this->output) {
            $this->output->write('Clearing cache on remote installation');
        }

        $this->type->clearCache($this);

        $zibo->triggerEvent(self::EVENT_POST_DEPLOY, $this);

        if ($this->output) {
            $this->output->write('Deployed to ' . $this->profile->getServer());
        }

        $this->pathApplication = null;
        $this->pathPublic = null;
        $this->type = null;
        $this->profile = null;
        $this->zibo = null;
    }

    /**
     * Updates the bootstrap config on the remote server
     * @param string $config Remote path to the config
     * @param string $environment Name of the deploy environment
     * @return null
     */
    protected function updateBootstrap($config) {
        $tempFile = File::getTemporaryFile('bootstrap');

        $bootstrap = new BootstrapConfig();
        $bootstrap->read(new File(ZIBO_CONFIG));
        $bootstrap->setCoreDirectory(new File($this->profile->getApplicationPath()));
        $bootstrap->setApplicationDirectory(new File($this->profile->getApplicationPath()));
        $bootstrap->setPublicDirectory(new File($this->profile->getPublicPath()));
        $bootstrap->removeModulesDirectories();
        $bootstrap->setEnvironment($this->profile->getEnvironment());
        $bootstrap->write($tempFile);

        $this->type->copyFile($this, $tempFile->getAbsolutePath(), $config);

        $tempFile->delete();
    }

    /**
     * Updates the provided script with the new config and syncs it with the
     * remote server
     * @param zibo\library\filesystem\File $source Source script
     * @param string $destination Path on the remote server
     * @param string $method Name of the method to use
     * @param string $path New path for the method
     * @return null
     */
    protected function updateScript(File $source, $destination, $method, $path) {
        if (!$source->exists()) {
            return;
        }

        $tempFile = File::getTemporaryFile('script');

        $source->copy($tempFile);

        $bootstrap = new BootstrapConfig();
        $bootstrap->$method($tempFile, $path);

        $this->type->copyFile($this, $tempFile->getAbsolutePath(), $destination);

        $tempFile->delete();
    }

    /**
     * Executes a command on the local server
     * @param string $command
     * @return string
     */
    public function executeLocalCommand($command) {
        return passthru($command);
    }

    /**
     * Executes a Zibo console command on the local server
     * @param string $command
     * @return string
     */
    public function executeLocalConsoleCommand($command) {
        if (!$this->pathApplication) {
            throw new Exception('Could not execute local console command: only available while deploying');
        }

        $binary = $this->zibo->getParameter('system.binary.php', 'php');

        $command = $binary . ' ' . $this->pathApplication . '/console.php ' . $command;

        return $this->executeLocalCommand($command);
    }

}