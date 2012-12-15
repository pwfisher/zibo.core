<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;
use zibo\core\Zibo;

/**
 * Interface for a console command
 */
interface Command {

    /**
     * Gets the name of the command
     * @return string
     */
	public function getName();

	/**
	 * Gets a short description of the command
	 * @return string
	 */
	public function getDescription();

	/**
	 * Gets the definitions of the arguments
	 * @return array Array of Argument instances
	 * @see zibo\core\console\command\CommandArgument
	 */
	public function getArguments();

	/**
	 * Gets the syntax of the command
	 * @return string
	 */
	public function getSyntax();

	/**
	 * Interpret the command
	 * @param zibo\core\console\InputValue $input The input value
	 * @param zibo\core\console\output\Output $output Output implementation
	 * @return null
	 */
	public function execute(InputValue $input, Output $output);

	/**
	 * Sets the instance of Zibo to the command
	 * @return string
	 */
	public function setZibo(Zibo $zibo);

}