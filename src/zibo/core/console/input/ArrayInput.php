<?php

namespace zibo\core\console\input;

use zibo\core\console\command\ExitCommand;

use zibo\library\Callback;

/**
 * Input a predefined set of input values
 */
class ArrayInput implements Input {

    /**
     * The input values
     * @var array
     */
    private $inputArray;

    /**
     * The next input
     * @var string
     */
    private $input;

    /**
     * The callback to invoke when the last input is fetched
     * @var zibo\library\Callback
     */
    private $callback;

    /**
     * Constructs a new array input
     * @param array $input The input values
     * @param callback $onFinish The callback to invoke when the last input is
     * fetched
     * @return null
     */
    public function __construct(array $input, $onFinish = null) {
        if ($onFinish) {
            $this->callback = new Callback($onFinish);
        }

        $this->inputArray = $input;
        $this->input = $this->getNextInput();
    }

    /**
     * Reads a line from the input
     * @param string $prompt The prompt for the input
     * @return string The input
     */
    public function read($prompt) {
        $input = $this->input;

        $this->input = $this->getNextInput();

        echo $prompt . $input . "\n";

        return $input;
    }

    /**
     * Gets the next input of the array.
     * @return string The next input. If all input is traversed, the onFinish
     * callback will be invoked and the exit command will be returned
     */
    protected function getNextInput() {
        $value = each($this->inputArray);
        if ($value !== false) {
            return $value[1];
        }

        if ($this->callback) {
            $this->callback->invoke();
            $this->callback = null;
        }

        return ExitCommand::NAME;
    }

}