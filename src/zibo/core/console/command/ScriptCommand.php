<?php

namespace zibo\core\console\command;

use zibo\core\console\input\ArrayInput;
use zibo\core\console\output\Output;
use zibo\core\console\Console;
use zibo\core\console\InputValue;

use zibo\library\filesystem\File;

/**
 * Command to execute a script of console commands
 */
class ScriptCommand extends AbstractCommand {

    /**
     * Instance of the console
     * @var zibo\core\console\Console
     */
    private $console;

    /**
     * The input of the console
     * @var zibo\core\console\input\Input
     */
    private $input;

    /**
     * Constructs a new script command
     * @param zibo\core\console\Console $console Instance of the console
     * @return null
     */
    public function __construct(Console $console) {
        $this->console = $console;

        parent::__construct('script', 'Execute a script of console commands.');
        $this->addArgument('file', 'Path of the script file');
    }

    /**
     * Interpret the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $file = $input->getArgument('file');

        // read the commands
        $file = new File($file);
        $contents = $file->read();
        $commands = explode("\n", $contents);

        // override the input of the console with the commands of the script
        $scriptInput = new ArrayInput($commands, array($this, 'onFinish'));

        $this->input = $this->console->getInput();
        $this->console->setInput($scriptInput);
    }

    /**
     * When finished, reset the input of the console
     * @return null
     */
    public function onFinish() {
        if (!$this->input) {
            return;
        }

        $this->console->setInput($this->input);
    }

}