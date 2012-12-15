<?php

namespace zibo\core\console\command;

use zibo\core\console\exception\ConsoleException;
use zibo\core\Zibo;

/**
 * Abstract implementation of a command
 */
abstract class AbstractCommand implements Command {

    /**
     * The name of this command
     * @var string
     */
	private $name;

	/**
	 * The short description of this command
	 * @var string
	 */
	private $description;

	/**
	 * The definitions of the arguments
	 * @var array
	 */
	private $arguments;

	/**
	 * Instance of Zibo
	 * @var zibo\core\Zibo
	 */
	protected $zibo;

	/**
	 * Constructs a new command
	 * @param string $name The command
	 * @param string $description A short description of the command
	 * @param string $syntax The syntax of this command
	 * @return null
	 */
	public function __construct($name, $description = null) {
		$this->setName($name);
		$this->setDescription($description);
		$this->arguments = array();
	}

	/**
	 * Sets the name of this command
	 * @param string $name
	 * @return null
	 * @throws Exception when the name is invalid
	 */
	private function setName($name) {
		if (!is_string($name) || !$name) {
			throw new ConsoleException('Provided name is empty or invalid');
		}

		$this->name = $name;
	}

	/**
	 * Gets the name of this command
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the short description of this command
	 * @param string $description
	 * @return null
	 * @throws Exception when the description is invalid
	 */
	private function setDescription($description) {
		if ($description !== null && (!is_string($description) || !$description)) {
			throw new ConsoleException('Provided description is empty or invalid');
		}

		$this->description = $description;
	}

	/**
	 * Gets a short description of this command
	 * @return string|null
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Gets the syntax of this command
	 * @return string|null
	 */
	public function getSyntax() {
		$optionalArguments = 0;

		$syntax = $this->name;
		foreach ($this->arguments as $argument) {
			if ($argument->isRequired()) {
				$syntax .= ' <' . $argument->getName() . '>';
				continue;
			}

			$optionalArguments++;
			$syntax .= ' [<' . $argument->getName() . '>';
		}

		$syntax .= str_repeat(']', $optionalArguments);

		return $syntax;
	}

	/**
	 * Adds the definition of a argument for this command. Arguments should be
	 * added in the order of the syntax, optional arguments as last
	 * @param string name Name of the argument
	 * @param string description Description of the argument
	 * @param boolean $isRequired Flag to see if the argument is required
	 * @param boolean $isDynamic Flag to see if the argument is dynamic
	 * @return null
	 * @throws zibo\core\console\exception\ConsoleException when the previous
	 * argument is optional and this one is not
	 */
	public function addArgument($name, $description, $isRequired = true, $isDynamic = false) {
		$argument = new CommandArgument($name, $description, $isRequired, $isDynamic);

		if ($this->arguments) {
			list($lastArgument) = array_slice($this->arguments, -1);

			if ($lastArgument->isDynamic()) {
				throw new ConsoleException("Cannot add a argument after a dynamic argument");
			}

			if (!$lastArgument->isRequired() && $argument->isRequired()) {
				throw new ConsoleException("Cannot add a required argument after a optional argument");
			}
		}

		$this->arguments[] = $argument;
	}

	/**
	 * Gets the definitions of the arguments
	 * return array
	 * @see CommandArgument
	 */
	public function getArguments() {
		return $this->arguments;
	}

    /**
     * Sets the instance of Zibo to the command
     * @return string
     */
	public function setZibo(Zibo $zibo) {
	    $this->zibo = $zibo;
	}

}