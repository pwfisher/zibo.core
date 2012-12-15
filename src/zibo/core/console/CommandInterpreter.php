<?php

namespace zibo\core\console;

use zibo\core\console\command\Command;
use zibo\core\console\command\ZiboCommand;
use zibo\core\console\exception\ArgumentNotSetException;
use zibo\core\console\exception\CommandNotFoundException;
use zibo\core\console\exception\ConsoleException;
use zibo\core\console\exception\InvalidArgumentCountException;
use zibo\core\console\output\Output;
use zibo\core\Zibo;

use zibo\library\String;

use \Exception;

/**
 * Interpreter for the console commands
 */
class CommandInterpreter implements AutoCompletable {

	/**
	 * Instance of Zibo
	 * @var zibo\core\Zibo
	 */
	private $zibo;

    /**
     * The registered commands
     * @var array
     */
	private $commands;

	/**
	 * Constructs a new command interpreter
	 */
	public function __construct(Zibo $zibo) {
		$this->zibo = $zibo;
		$this->commands = array();
	}

	/**
	 * Interprets the provided command
	 * @param string $input The input to interpret
	 * @param zibo\core\console\output\Output $output Output implementation
	 * @return null
	 * @throws Exception when the command does not exist
	 */
	public function interpret($input, Output $output) {
		// find the command
		$command = null;
		foreach ($this->commands as $commandName => $commandInstance) {
			if (!String::startsWith($input, $commandName)) {
				continue;
			}

			if (!$command || strlen($command->getName()) < strlen($commandName)) {
				$command = $commandInstance;
			}
		}

		if (!$command) {
	        throw new CommandNotFoundException($input);
		}

		// parse the arguments
	    $input = new InputValue($input, substr_count($command->getName(), ' '));

	    $arguments = $command->getArguments();

	    $index = 0;
	    foreach ($arguments as $argument) {
	    	if ($argument->isRequired()) {
	    		$value = $input->getArgument($index);
	    		if ($value === null || $value == '') {
	    			throw new ArgumentNotSetException($argument->getName());
	    		}
	    	}

	    	if ($input->hasArgument($index)) {
		    	if ($argument->isDynamic()) {
	    			$input->nameDynamicArgument($index, $argument->getName());
		    	} else {
	    			$input->nameArgument($index, $argument->getName());
		    	}
	    	}

	    	$index++;
	    }

	    if (count($arguments) < $input->getArgumentCount()) {
	    	throw new InvalidArgumentCountException();
	    }

	    // execute the command
	    $command->execute($input, $output);
	}

    /**
     * Performs auto complete on the provided input
     * @param string $input The input value to auto complete
     * @return array|null Array with the auto completion matches or null when
     * no auto completion is available
     */
	public function autoComplete($input) {
	    $commands = array();

	    foreach ($this->commands as $commandName => $commandInstance) {
	    	if (!String::startsWith($commandName, $input) && !String::startsWith($input, $commandName)) {
	    		continue;
	    	}

	    	$commands[$commandName] = $commandInstance;
	    }

	    $tokens = explode(' ', $input);
	    $numTokens = count($tokens);

	    $completion = array();
	    foreach ($commands as $index => $command) {
	    	$commandName = $command->getName();

	    	$commandTokens = explode(' ', $commandName);
	    	$numCommandTokens = count($commandTokens);

	    	if ($numTokens < $numCommandTokens) {
	    		$commandName = '';
	    		for ($i = 0; $i < $numTokens; $i++) {
	    			$commandName .= ($commandName ? ' ' : '') . $commandTokens[$i];
	    		}

	    		$completion[$commandName] = $commandName;
	    	} elseif ($numTokens == $numCommandTokens) {
	    		$completion[$commandName] = $commandName;
	    	} else {
	    	    unset($commands[$index]);

	    	    if ($command instanceof AutoCompletable) {
	    	        $commandInput = substr($input, strlen($commandName) + 1);
	    	        $commandCompletion = $command->autoComplete($commandInput);

	    	        foreach ($commandCompletion as $commandAutoComplete) {
	    	            $completion[$commandName . ' ' . $commandAutoComplete] = $commandName . ' ' . $commandAutoComplete;
	    	        }
	    	    }
	    	}
	    }

	    return $completion;
	}

	/**
	 * Registers a command in the interpreter
	 * @param zibo\core\console\command\Command $command The command to register
	 * @return null
	 */
	public function registerCommand(Command $command) {
        $command->setZibo($this->zibo);

	    $this->commands[$command->getName()] = $command;
	}

	/**
	 * Unregisters a command
	 * @param string $name Name of the command
	 * @throws Exception when the command is not registered
	 */
	public function unregisterCommand($name) {
		if (!$this->hasCommand($name)) {
			throw new ConsoleException('Command ' . $name . ' is not registered');
		}

		unset($this->commands[$name]);
	}

	/**
	 * Checks if a command is registered
	 * @param string $name
	 * @return boolean True when the command is registered, false otherwise
	 * @throws Exception when the provided name is empty or invalid
	 */
	public function hasCommand($name) {
	    if (!is_string($name) || !$name) {
	        throw new ConsoleException('Provided name is invalid or empty');
	    }

		return isset($this->commands[$name]);
	}

	/**
	 * Gets a command by its name
	 * @param string $name
	 * @return zibo\core\console\command\Command
	 * @throws Exception when the command is not registered
	 */
	public function getCommand($name) {
		if (!$this->hasCommand($name)) {
			throw new ConsoleException('Command ' . $name . ' is not registered');
		}

		return $this->commands[$name];
	}

	/**
	 * Gets all the commands
	 * @return array Array with the name of the command as key and an instance
	 * of Command as value
	 * @see zibo\core\console\command\Command
	 */
	public function getCommands() {
		return $this->commands;
	}

}