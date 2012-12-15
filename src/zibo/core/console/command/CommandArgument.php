<?php

namespace zibo\core\console\command;

use zibo\core\console\exception\ConsoleException;

/**
 * Definition of a command argument
 */
class CommandArgument {

    /**
     * Name of this argument
     * @var string
     */
	private $name;

	/**
	 * Description of this argument
	 * @var string
	 */
	private $description;

	/**
	 * Flag to see if this argument is required
	 * @var boolean
	 */
	private $isRequired;

	/**
	 * Flag to see if this argument is dynamic
	 * @var boolean
	 */
	private $isDynamic;

	/**
	 * Constructs a new command argument
	 * @param string $name Name of the argument
	 * @param string $description Description of the argument
	 * @param string $isRequired Flag to see if the argument is required
	 * @param string $isDynamic Flag to see if the argument is dynamic
	 * @return null
	 */
	public function __construct($name, $description = null, $isRequired = false, $isDynamic = false) {
		$this->setName($name);
		$this->setDescription($description);
		$this->setIsRequired($isRequired);
		$this->setIsDynamic($isDynamic);
	}

	/**
	 * Gets a string representation of this argument
	 * @return string
	 */
	public function __toString() {
		if (!$this->isRequired) {
			$string = '[<' . $this->name . '>]';
		} else {
			$string = '<' . $this->name . '>';
		}

		if ($this->description) {
			$string .= ' ' . $this->description;
		}

		return $string;
	}

	/**
	 * Sets the name of this argument
	 * @param string $name
	 * @return null
	 * @throws zibo\core\console\exception\ConsoleException when the
	 * name is invalid
	 */
	private function setName($name) {
		if (!is_string($name) || !$name) {
			throw new ConsoleException('Provided name is empty or invalid');
		}

		$this->name = $name;
	}

	/**
	 * Gets the name of this argument
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the description of this argument
	 * @param string $description
	 * @return null
	 * @throws zibo\core\console\exception\ConsoleException when the
	 * description is invalid
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
	 * Sets whether this argument is required
	 * @param boolean $flag
	 * @return null
	 * @throws zibo\core\console\exception\ConsoleException when the
	 * flag is not a boolean
	 */
	private function setIsRequired($flag) {
		if (!is_bool($flag)) {
			throw new ConsoleException('Provided flag is invalid');
		}

		$this->isRequired = $flag;
	}

	/**
	 * Gets whether this argument is required
	 * @return boolean
	 */
	public function isRequired() {
		return $this->isRequired;
	}

	/**
	 * Sets whether this argument is dynamic. A dynamic argument takes all the
	 * remaining input, therefor there can be only one.
	 * @param boolean $flag
	 * @return null
	 * @throws zibo\core\console\exception\ConsoleException when the
	 * flag is not a boolean
	 */
	private function setIsDynamic($flag) {
		if (!is_bool($flag)) {
			throw new ConsoleException('Provided syntax is empty or invalid');
		}

		$this->isDynamic = $flag;
	}

	/**
	 * Gets whether this argument is dynamic. A dynamic argument takes all the
	 * remaining input, therefor there can be only one.
	 * @return boolean
	 */
	public function isDynamic() {
		return $this->isDynamic;
	}

}