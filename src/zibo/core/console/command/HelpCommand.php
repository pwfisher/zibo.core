<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\CommandInterpreter;
use zibo\core\console\InputValue;
use zibo\core\Zibo;

/**
 * Command to view and modify the configuration
 */
class HelpCommand extends AbstractCommand {

    /**
     * The name of this command
     * @var string
     */
    const NAME = 'help';

    /**
     * Instance of the interpreter
     * @var CommandInterpreter
     */
    private $interpreter;

    /**
     * Constructs a new config command
     * @return null
     */
    public function __construct(CommandInterpreter $interpreter) {
        $this->interpreter = $interpreter;

        parent::__construct(self::NAME, 'Prints this help.');
		$this->addArgument('command', 'Provide a name of a command to get the detailed help of the command', false, true);
    }

    /**
     * Interpret the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
    	$command = $input->getArgument('command');
    	if ($command) {
    		$this->showCommand($output, $command);
    	} else {
    		$this->showOverview($output);
    	}
    }

    /**
     * Writes the help of a command to the output
     * @param zibo\core\console\output\Output $output Output interface
     * @param string $command Name of the command
     * @return null
     */
    private function showCommand(Output $output, $command) {
    	$command = $this->interpreter->getCommand($command);
    	$description = $command->getDescription();
    	$arguments = $command->getArguments();
    	$flags= $command->getFlags();

    	if ($description) {
			$output->write('');
    		$output->write($description);
    	}

        $output->write('');
    	$output->write('Syntax: ' . $command->getSyntax());
    	foreach ($flags as $flag => $description) {
    	    $output->write('- [--' . $flag . '] ' . $description);
    	}
		foreach ($arguments as $argument) {
			$output->write('- ' . $argument);
		}
        $output->write('');
    }

    /**
     * Writes an overview of all the commands to the output
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    private function showOverview(Output $output) {
    	$environment = $this->zibo->getEnvironment()->getName();

        $output->write('Zibo ' . Zibo::VERSION . ' console (' . $environment . ').');
        $output->write('');
        $output->write('The Zibo console can be used to run maintenance tasks on your installation.');
        $output->write('');
        $output->write('Available flags:');
        $output->write('- --debug  Show the full stack trace of runtime exceptions.');
        $output->write('- --shell  Runs the console in a interactive shell.');
        $output->write('');
        $output->write('If you are in a interactive shell, you can use tab for command auto completion and the up and down arrows for command history.');
        $output->write('');
        $output->write('Available commands:');

        $commands = $this->interpreter->getCommands();
        ksort($commands);

        foreach ($commands as $command) {
            $output->write('- ' . $command->getSyntax());
        }

        $output->write('');
        $output->write('Type \'help <command>\' to get help for a specific command.');
        $output->write('');
    }

}