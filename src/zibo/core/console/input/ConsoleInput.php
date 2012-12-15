<?php

namespace zibo\core\console\input;

use zibo\core\console\command\ExitCommand;
use zibo\core\console\command\HelpCommand;
use zibo\core\console\InputValue;

/**
 * Implementation of input to take 1 command from the command line
 */
class ConsoleInput implements Input {

    /**
     * Flag to see if a read has been done
     * @var boolean
     */
    private $isFirstRead = true;

    /**
     * Reads a line from the input
     * @param string $prompt The prompt for the input
     * @return string The input
     */
    public function read($prompt) {
        if (!$this->isFirstRead) {
            return ExitCommand::NAME;
        }

        $this->isFirstRead = false;
        $input = null;

        if (isset($_SERVER['argv'])) {
            $args = $_SERVER['argv'];
            $input = implode(' ', $args);

            $input = new InputValue($input);
            $args = $input->getArguments();
            $flags = $input->getFlags();

            $input = implode(' ', $args);

            if ($flags) {
                foreach ($flags as $flag => $value) {
                    $input .= ' --' . $flag;
                    if ($value !== true) {
                        $input .= '"' . $value . '"';
                    }
                }
            }
        }

        if (!$input) {
            $input = HelpCommand::NAME;
        }

        return $input;
    }

}