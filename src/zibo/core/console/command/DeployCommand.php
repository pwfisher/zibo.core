<?php

namespace zibo\core\console\command;

use zibo\core\build\Builder;
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
     * Parameter name of the environment
     * @var string
     */
    const PARAM_ENVIRONMENT = 'environment';

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
        $this->addFlag('force', 'Force deployment of a dev environment');
    }

    /**
     * Interpret the command
     * @param zibo\core\console\InputValue $input The input
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $destination = $input->getArgument('destination');
        $profile = $input->getArgument('profile');

        $environment = $this->zibo->getEnvironment()->getName();
        if ($environment == 'dev') {
            if (!$input->hasFlag('force')) {
                $output->write('Are you sure you want to build and deploy your dev environment at once? Add the --force flag to your command to actually perform this task.');
            } else {
                $this->buildAndDeploy($profile, $output);
            }
        } else {
            $params = $this->zibo->getParameter(self::PARAM_DEPLOY . $profile);
            if (!is_array($params)) {
                throw new Exception('Could not deploy ' . $profile . ': no profile defined');
            }
            $params = Config::flattenConfig($params);

            $this->deploy($profile, $params, $output);
        }
    }

    /**
     * Builds to a temporary directory and deploys from there
     * @param string $profile Name of the deploy profile
     * @param zibo\core\console\output\Output $output
     * @return null
     */
    protected function buildAndDeploy($profile, Output $output) {
        $file = File::getTemporaryFile('build');
        $file->delete();
        $file->create();

        $builder = new Builder();
        $builder->build($this->zibo, $file);

        $phpBinary = $this->zibo->getParameter('system.binary.php', 'php');

        passthru($phpBinary . ' ' . $file . '/application/console.php deploy ' . $profile);

        $file->delete();

        $output->write('Deleted ' . $file);
    }

    /**
     * Perform deployment of this installation
     * @param array $params
     * @throws Exception
     */
    protected function deploy($profile, $params, Output $output) {
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

        if (isset($params[self::PARAM_ENVIRONMENT])) {
            $environment = $params[self::PARAM_ENVIRONMENT];
            unset($params[self::PARAM_ENVIRONMENT]);
        } else {
            $environment = 'prod';
        }

        $output->write('Deploying to environment ' . $environment . ' from profile ' . $profile);

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