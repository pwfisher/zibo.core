<?php

namespace zibo\core\console\input;

/**
 * Interface for the input in a CLI environment
 */
interface Input {

    /**
     * Reads a line from the input
     * @param string $prompt The prompt for the input
     * @return string The input
     */
    public function read($prompt);

}