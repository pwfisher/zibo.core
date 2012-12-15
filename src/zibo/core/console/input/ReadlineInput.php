<?php

namespace zibo\core\console\input;

use zibo\core\console\exception\ConsoleException;
use zibo\core\console\AutoCompletable;

/**
* Readline implementation for input in a CLI environment
*/
class ReadlineInput implements AutoCompletableInput {

    /**
     * The registered auto completions
     * @var array
     */
    protected $autoCompletions;

    /**
     * Constructs a new Readline input
     * @throws Exception when the Readline PHP extension is not available
     */
    public function __construct() {
        if (!function_exists('readline')) {
            throw new ConsoleException('The Readline PHP extension is not installed or not enabled. Check your PHP installation.');
        }

        $this->autoCompletions = array();
    }

    /**
     * Adds a auto completion implementation to this input
     * @param zibo\core\console\AutoCompletable $autoCompletable
     * @return null
     */
    public function addAutoCompletion(AutoCompletable $autoCompletable) {
        if (!$this->autoCompletions) {
            readline_completion_function(array($this, 'performAutoComplete'));
        }

        $this->autoCompletions[] = $autoCompletable;
    }

    /**
     * Performs auto complete on the provided input
     * @param string $input The input value
     * @return array|null Array with the auto completion matches or null when
     * no auto completion is available
     */
    public function performAutoComplete($string, $position) {
        // get the full input
        $info = readline_info();
        $input = substr($info['line_buffer'], 0, $info['end']);

        // get all the matches
        $matches = array();
        foreach ($this->autoCompletions as $autoCompletable) {
            $matches = $autoCompletable->autoComplete($input);
            if ($matches) {
                break;
            }
        }

        // process the matches, make sure a space is added and remove the
        // input from the matches
        foreach ($matches as $matchIndex => $match) {
			$matches[$matchIndex] = substr($match, $position);

            if ($matches[$matchIndex] == '' || $input == $match) {
                unset($matches[$matchIndex]);
                continue;
            }

            if (substr($match, -1) != ' ') {
                $matches[$matchIndex] .= ' ';
            }
        }

        // return the result
        if (!$matches) {
            return null;
        }

        return $matches;
    }

    /**
     * Reads a line from the input
     * @param string $prompt The prompt for the input
     * @return string The input
     */
    public function read($prompt) {
        $input = readline($prompt);

        if (!empty($input)) {
            readline_add_history($input);
        }

        return $input;
    }

}