<?php

namespace zibo\core\console;

use zibo\core\console\exception\ConsoleException;

/**
 * A parsed input value
 */
class InputValue {

    /**
     * The full input
     * @var string
     */
    protected $input;

    /**
     * The command of input, the first token
     * @var string
     */
    protected $command;

    /**
     * The arguments which are values on itself (numeric)
     * @var array
     */
    protected $arguments;

    /**
     * The flags and named arguments
     * @var array
     */
    protected $flags;

    /**
     * Constructs a new input value
     * @param string $input A full input string
     * @param integer $argumentOffset Number of arguments which are actually
     * part of the command
     * @return null
     */
    public function __construct($input, $argumentOffset = 0) {
        $this->setInput($input, $argumentOffset);
    }

    /**
     * Sets the input and parses it into the command, arguments and flags
     * @param string $input The input string
     * @return null
     */
    protected function setInput($input, $argumentOffset) {
        $this->input = $input;
        $this->arguments = array();
        $this->flags = array();

        $position = strpos($input, ' ');
        if ($position === false) {
            $this->command = $input;
            return;
        }

        $this->command = substr($input, 0, $position);

        $arguments = substr($input, $position);
        $arguments = ArgumentParser::getArguments($arguments);
        $arguments = ArgumentParser::parseArguments($arguments);

        foreach ($arguments as $key => $value) {
            if (is_numeric($key)) {
            	if ($argumentOffset) {
					$this->command .= ' ' . $value;
					$argumentOffset--;
            	} else {
                	$this->arguments[] = $value;
            	}
            } else {
                $this->flags[$key] = $value;
            }
        }
    }

    /**
     * Gets the unparsed input value
     * @return string
     */
    public function getInput() {
        return $this->input;
    }

    /**
     * Gets the command of the input, the first token
     * @return string
     */
    public function getCommand() {
        return $this->command;
    }

    /**
     * Checks if a argument is set
     * @return boolean True if set, false otherwise
     */
    public function hasArguments() {
        return $this->arguments ? true : false;
    }

    /**
     * Checks if a argument is set
     * @param mixed $index Index of the argument
     * @return boolean True if set, false otherwise
     */
    public function hasArgument($index) {
        return isset($this->arguments[$index]);
    }

    /**
     * Gets a argument by its index
     * @param mixed $index Index of the argument
	 * @param mixed $default Default value for when the argument is not set
	 * @return mixed The value of the argument if set, the default value otherwise
	 */
    public function getArgument($index, $default = null) {
        if (!isset($this->arguments[$index])) {
            return $default;
        }

        return $this->arguments[$index];
    }

    /**
     * Gets the arguments of this input
     * @return array Array with numeric keys
     */
    public function getArguments() {
        return $this->arguments;
    }

    /**
     * Gets the number of arguments
     * @return integer Number of arguments
     */
    public function getArgumentCount() {
        return count($this->arguments);
    }

    /**
     * Gives a indexed argument a name
     * @param mixed $index The current index of the argument
     * @param string $name The name of the argument
     * @return null
     * @throws zibo\core\console\exception\ConsoleException when no argument is
     * set with the provided index
     */
    public function nameArgument($index, $name) {
    	if (!isset($this->arguments[$index])) {
    		throw new ConsoleException('No argument set at index ' . $index);
    	}

    	$this->arguments[$name] = $this->arguments[$index];
    	unset($this->arguments[$index]);
    }

    /**
     * Gives a indexed argument a name and concats all the following arguments
     * to it
     * @param mixed $index The current index of the argument
     * @param string $name The name of the argument
     * @return null
     * @throws zibo\core\console\exception\ConsoleException when no argument is
     * set with the provided index
     */
    public function nameDynamicArgument($index, $name) {
    	$this->nameArgument($index, $name);

    	$index++;

    	$numArguments = $this->getArgumentCount();
		for ($i = $index; $i < $numArguments; $i++) {
			$this->arguments[$name] .= ' ' . $this->arguments[$i];
			unset($this->arguments[$i]);
		}
    }

    /**
     * Checks if a flag is set
     * @return boolean True if set, false otherwise
     */
    public function hasFlags() {
        return $this->flags ? true : false;
    }

    /**
     * Checks if a flag is set
     * @param string $name The name of the flag
     * @return boolean True if set, false otherwise
     */
    public function hasFlag($name) {
        return isset($this->flags[$name]);
    }

    /**
     * Gets a flag by its name
     * @param string $name The name of the flag
     * @param mixed $default The default value for when the flag is not set
     * @return mixed The value of the flag if set, the default value otherwise
     */
    public function getFlag($name, $default = null) {
        if (!isset($this->flags[$name])) {
            return $default;
        }

        return $this->flags[$name];
    }

    /**
     * Gets the flags of this input
     * @return array Array with named keys
     */
    public function getFlags() {
        return $this->flags;
    }

}