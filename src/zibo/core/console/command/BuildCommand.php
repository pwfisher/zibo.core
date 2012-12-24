<?php

namespace zibo\core\console\command;

use zibo\core\build\Builder;
use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

use zibo\library\filesystem\File;

/**
 * Command to build your installation to a optimized state
 */
class BuildCommand extends AbstractCommand {

    /**
     * Constructs a new config command
     * @return null
     */
    public function __construct() {
        parent::__construct('build', 'Builds your current Zibo into the most performant state.');
        $this->addArgument('destination', 'Path of the destination directory');
        $this->addArgument('environment', 'Name of the environment (default: prod)', false);
    }

    /**
     * Interpret the command
     * @param zibo\core\console\InputValue $input The input
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $destination = $input->getArgument('destination');
        $environment = $input->getArgument('environment', 'prod');

        $file = new File($destination);

        $builder = new Builder();
        $builder->build($this->zibo, $file, $environment);
    }

}