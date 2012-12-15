<?php

namespace zibo\core\console\output;

/**
 * Implementation to echo the output through the echo command
 */
class PhpOutput implements Output {
	
	/**
	 * Writes the output
	 * @param string $output
	 * @return null
	 */
	public function write($output) {
		echo $output . "\n";
	}
	
}