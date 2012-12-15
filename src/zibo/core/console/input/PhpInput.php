<?php

namespace zibo\core\console\input;

/**
 * PHP implementation for input in a CLI environment
 */
class PhpInput implements Input {

    /**
     * Reads a line from the input
     * @param string $prompt The prompt for the input
     * @return string The input
     */
    public function read($prompt) {
        echo $prompt;

        return trim(fgets(STDIN));
    }

}