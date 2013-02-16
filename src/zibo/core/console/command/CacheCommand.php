<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to get an overview of the caches
 */
class CacheCommand extends AbstractCommand {

    /**
     * Constructs a new cache clear command
     * @return null
     */
    public function __construct() {
        parent::__construct('cache', 'Gets an overview of the caches');
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $controls = $this->zibo->getDependencies('zibo\\core\\cache\\control\\CacheControl');

        ksort($controls);

        foreach ($controls as $name => $control) {
            $output->write('[' . ($control->isEnabled($this->zibo) ? 'X' : ' ') . '] ' . $name . (!$control->canToggle() ? ' (locked)' : ''));
        }
    }

}