<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to enable the cache
 */
class CacheEnableCommand extends AbstractCommand {

    /**
     * Constructs a new cache enable command
     * @return null
     */
    public function __construct() {
        parent::__construct('cache enable', 'Enables the cache');
        $this->addArgument('name', 'Name of the cache to enable', false);
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $name = $input->getArgument('name');

        if ($name) {
            $control = $this->zibo->getDependency('zibo\\core\\cache\\control\\CacheControl', $name);
            if ($control->canToggle()) {
                $control->enable($this->zibo);
            }
        } else {
            $controls = $this->zibo->getDependencies('zibo\\core\\cache\\control\\CacheControl');
            foreach ($controls as $control) {
                if ($control->canToggle()) {
                    $control->enable($this->zibo);
                }
            }
        }
    }

}