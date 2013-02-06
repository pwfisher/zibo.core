<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to execute PHP code
 */
class PhpCommand extends AbstractCommand {

    /**
     * The name of this command
     * @var string
     */
    const NAME = 'php';

    /**
     * Constructs a new exit command
     * @param string $name The name of the exit command
     * @return null
     */
    public function __construct() {
        parent::__construct(self::NAME, 'Executes PHP code.');
        $this->addArgument('code', 'PHP code');
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        // dummy command, the real php is in the console
    }

}