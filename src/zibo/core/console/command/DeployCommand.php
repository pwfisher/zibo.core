<?php

namespace zibo\core\console\command;

use zibo\core\build\Builder;
use zibo\core\console\output\Output;
use zibo\core\console\InputValue;
use zibo\core\deploy\Deployer;
use zibo\core\deploy\DeployProfile;

use zibo\library\filesystem\File;

/**
 * Command to deploy your installation to a remote server
 */
class DeployCommand extends AbstractCommand {

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

        $profileIO = $this->zibo->getDependency('zibo\\core\\deploy\\io\\DeployProfileIO');
        $profile = $profileIO->readProfile($this->zibo, $profile);

        $environment = $this->zibo->getEnvironment()->getName();
        if ($environment == 'dev') {
            if (!$input->hasFlag('force')) {
                $output->write('Are you sure you want to build and deploy your dev environment at once? Add the --force flag to your command to actually perform this task.');
            } else {
                $this->buildAndDeploy($profile, $output);
            }
        } else {
            $this->deploy($profile, $output);
        }
    }

    /**
     * Builds to a temporary directory and deploys from there
     * @param zibo\core\deploy\DeployProfile $profile Deploy profile
     * @param zibo\core\console\output\Output $output
     * @return null
     */
    protected function buildAndDeploy(DeployProfile $profile, Output $output) {
        $file = File::getTemporaryFile('build');
        $file->delete();
        $file->create();

        $builder = new Builder();
        $builder->setOutput($output);
        $builder->build($this->zibo, $file, $profile->getEnvironment());

        $phpBinary = $this->zibo->getParameter('system.binary.php', 'php');

        passthru($phpBinary . ' ' . $file . '/application/console.php deploy ' . $profile->getName());

        $file->delete();

        $output->write('Deleted ' . $file);
    }

    /**
     * Perform deployment of this installation
     * @param zibo\core\deploy\DeployProfile $profile Deploy profile
     * @param zibo\core\console\output\Output $output
     * @throws Exception
     */
    protected function deploy(DeployProfile $profile, Output $output) {
        $deployer = new Deployer();
        $deployer->setOutput($output);
        $deployer->deploy($this->zibo, $profile);
    }

}