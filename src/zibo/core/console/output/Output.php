<?php

namespace zibo\core\console\output;

/**
 * Interface for the output of a command
 */
interface Output {
	
	/**
	 * Writes the output
	 * @param string $output
	 * @return null
	 */
	public function write($output);
	
}