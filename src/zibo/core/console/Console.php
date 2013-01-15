<?php

namespace zibo\core\console;

use zibo\core\console\command\CacheClearCommand;
use zibo\core\console\command\DependencySearchCommand;
use zibo\core\console\command\DeployCommand;
use zibo\core\console\command\FileSearchCommand;
use zibo\core\console\command\ExitCommand;
use zibo\core\console\command\HelpCommand;
use zibo\core\console\command\ParameterGetCommand;
use zibo\core\console\command\ParameterSearchCommand;
use zibo\core\console\command\ParameterSetCommand;
use zibo\core\console\command\ParameterUnsetCommand;
use zibo\core\console\command\RouterRegisterCommand;
use zibo\core\console\command\RouterRouteCommand;
use zibo\core\console\command\RouterSearchCommand;
use zibo\core\console\command\RouterUnregisterCommand;
use zibo\core\console\command\ScriptCommand;
use zibo\core\console\command\SessionCleanUpCommand;
use zibo\core\console\exception\CommandNotFoundException;
use zibo\core\console\exception\ConsoleException;
use zibo\core\console\input\AutoCompletableInput;
use zibo\core\console\input\Input;
use zibo\core\console\output\Output;
use zibo\core\Zibo;

use \Exception;

/**
 * Controller for the command line interface
 */
class Console {

    /**
     * Event to initialize the command interpreter
     * @var string
     */
    const EVENT_CONSOLE = 'console';

    /**
     * Instance of Zibo
     * @var zibo\core\Zibo
     */
    private $zibo;

    /**
     * Flag to see if debug mode is enabled
     * @var boolean
     */
    private $isDebug;

    /**
     * The command interpreter
     * @var zibo\core\console\CommandInterpreter
     */
    private $interpreter;

    /**
     * Prompt for the input
     * @var string
     */
    private $prompt;

    /**
     * Input interface
     * @var zibo\core\console\input\Input
     */
	private $input;

    /**
     * Output interface
     * @var zibo\core\console\output\Output
     */
	private $output;

    /**
     * Constructs a new console
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @param string $prompt Prompt for the input
     * @return null
     * @throw zibo\core\console\exception\ConsoleException when not in CLI mode
     */
    public function __construct(Zibo $zibo, $prompt = '> ') {
    	$environment = $zibo->getEnvironment();
    	if (!$environment->isCli()) {
    		throw new ConsoleException('The console can only be run in CLI mode');
    	}

    	$this->zibo = $zibo;
    	$this->isDebug = false;
    	$this->prompt = $prompt;
    	$this->interpreter = null;
    	$this->input = null;
    	$this->output = null;
    }

    /**
     * Run the console
     * @return null
     * @throws zibo\core\console\exception\ConsoleException when no input or
     * output has been set
     */
    public function run() {
        if (!$this->input) {
            throw new ConsoleException('No input interface set, use setInput() first.');
        }
        if (!$this->output) {
            throw new ConsoleException('No output interface set, use setOutput() first.');
        }

        // initialize the interpreter
        $this->initialize($this->zibo);

        // initialize auto completion
        if ($this->input instanceof AutoCompletableInput) {
            $this->input->addAutoCompletion($this->interpreter);
        }

        // run the interpreter loop
        do {
            // get the input
            $input = trim($this->input->read($this->prompt));

            if ($input == ExitCommand::NAME || $input == '') {
                // empty or exit command, next loop
                continue;
            }

            try {
                $this->interpreter->interpret($input, $this->output);
            } catch (CommandNotFoundException $e) {
            	// command not found, process as PHP code
                if (substr($input, -1) != ';') {
                    $input .= ';';
                }

                try {
                    eval($input);
                } catch (Exception $exception) {
                    $this->output->write('PHP interpreter: ' . $exception->getMessage());
                    if ($this->isDebug) {
                        $this->output->write($exception->getTraceAsString());
                    }
                }
            } catch (Exception $exception) {
                $message = $exception->getMessage();
                if (!$message) {
                    $message = get_class($exception);
                }

                $this->output->write('Error: ' . $message);
                if ($this->isDebug) {
                    $this->output->write($exception->getTraceAsString());
                }
            }
        } while ($input != ExitCommand::NAME);
    }

    /**
     * Initializes the console
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     * @see zibo\core\console\CommandInterpreter
     */
    public function initialize(Zibo $zibo) {
        $this->interpreter = new CommandInterpreter($zibo);
        $this->interpreter->registerCommand(new CacheClearCommand());
        $this->interpreter->registerCommand(new DependencySearchCommand());
        $this->interpreter->registerCommand(new DeployCommand());
        $this->interpreter->registerCommand(new ExitCommand());
        $this->interpreter->registerCommand(new FileSearchCommand());
        $this->interpreter->registerCommand(new HelpCommand($this->interpreter));
        $this->interpreter->registerCommand(new ParameterGetCommand());
        $this->interpreter->registerCommand(new ParameterSearchCommand());
        $this->interpreter->registerCommand(new ParameterSetCommand());
        $this->interpreter->registerCommand(new ParameterUnsetCommand());
        $this->interpreter->registerCommand(new RouterRegisterCommand());
        $this->interpreter->registerCommand(new RouterRouteCommand());
        $this->interpreter->registerCommand(new RouterSearchCommand());
        $this->interpreter->registerCommand(new RouterUnregisterCommand());
        $this->interpreter->registerCommand(new SessionCleanUpCommand());
        $this->interpreter->registerCommand(new ScriptCommand($this));

        $commands = $zibo->getDependencies('zibo\\core\\console\\command\\Command');
        foreach ($commands as $command) {
            $this->interpreter->registerCommand($command);
        }

        $zibo->triggerEvent(self::EVENT_CONSOLE, $this);
    }

    /**
     * Gets the command interpreter
     * @return \zibo\core\console\CommandInterpreter
     */
    public function getInterpreter() {
        return $this->interpreter;
    }

    /**
     * Sets the debug flag
     * @param boolean $isDebug
     * @return null
     */
    public function setIsDebug($isDebug) {
        $this->isDebug = $isDebug;
    }

    /**
     * Checks if the debug flag is on
     * @return boolean
     */
    public function isDebug() {
        return $this->isDebug;
    }

    /**
     * Sets the input interface
     * @param zibo\core\console\input\Input $input
     * @return null
     */
    public function setInput(Input $input) {
        $this->input = $input;
    }

    /**
     * Gets the input interface
     * @return zibo\core\console\input\Input
     */
    public function getInput() {
        return $this->input;
    }

    /**
     * Sets the output interface
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function setOutput(Output $output) {
    	$this->output = $output;
    }

    /**
     * Gets the output interface
     * @return zibo\core\console\output\Output
     */
    public function getOutput() {
    	return $this->output;
    }

}