<?php

namespace zibo\core\console\command;

use zibo\core\build\Deployer;
use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

use zibo\library\config\Config;
use zibo\library\filesystem\File;

use \Exception;

/**
 * Command to deploy your installation to a remote server
 */
class DeployCommand extends AbstractCommand {

    /**
     * Parameter prefix for a deploy profile
     * @var string
     */
    const PARAM_DEPLOY = 'deploy.';

    /**
     * Parameter name of the remote server
     * @var string
     */
    const PARAM_SERVER = 'server';

    /**
     * Parameter name of the remote application directory
     * @var string
     */
    const PARAM_APPLICATION = 'path.application';

    /**
     * Parameter name of the remote public directory
     * @var string
     */
    const PARAM_PUBLIC = 'path.public';

    /**
     * Parameter name of the path to the SSH key
     * @var string
     */
    const PARAM_SSH_KEY = 'ssh.key';

    /**
     * Constructs a new config command
     * @return null
     */
    public function __construct() {
        parent::__construct('deploy', 'Deploys your current Zibo to a remote server.');
        $this->addArgument('profile', 'Name of the deploy profile');
        $this->addArgument('environment', 'Name of the environment (default: current environment)', false);
    }

    /**
     * Interpret the command
     * @param zibo\core\console\InputValue $input The input
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $destination = $input->getArgument('destination');
        $profile = $input->getArgument('profile');
        $environment = $input->getArgument('environment');

        $params = $this->zibo->getParameter(self::PARAM_DEPLOY . $profile);
        if (!is_array($params)) {
            throw new Exception('Could not deploy ' . $profile . ': no profile defined');
        }

        $params = Config::flattenConfig($params);

        if (isset($params[self::PARAM_SERVER])) {
            $server = $params[self::PARAM_SERVER];
            unset($params[self::PARAM_SERVER]);
        } else {
            throw new Exception('Could not deploy ' . $profile . ': no server parameter set');
        }

        if (isset($params[self::PARAM_APPLICATION])) {
            $application = $params[self::PARAM_APPLICATION];
            unset($params[self::PARAM_APPLICATION]);
        } else {
            throw new Exception('Could not deploy ' . $profile . ': no path.application parameter set');
        }

        if (isset($params[self::PARAM_PUBLIC])) {
            $public = $params[self::PARAM_PUBLIC];
            unset($params[self::PARAM_PUBLIC]);
        } else {
            throw new Exception('Could not deploy ' . $profile . ': no path.public parameter set');
        }

        $deployer = new Deployer($server, $application, $public);

        if (isset($params[self::PARAM_SSH_KEY])) {
            $deployer->setSshKey($params[self::PARAM_SSH_KEY]);
            unset($params[self::PARAM_SSH_KEY]);
        }

        foreach ($params as $name => $value) {
            $deployer->setParameter($name, $value);
        }

        $deployer->deploy($this->zibo, $environment);
    }

}