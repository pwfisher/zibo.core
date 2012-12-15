<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to clear the cache
 */
class CacheClearCommand extends AbstractCommand {

    /**
     * Constructs a new cache clear command
     * @return null
     */
    public function __construct() {
        parent::__construct('cache clear', 'Clears the cache');
        $this->addArgument('name', 'Name of the cache to clear', false);
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
            $control->clear($this->zibo);
        } else {
            $controls = $this->zibo->getDependencies('zibo\\core\\cache\\control\\CacheControl');
            foreach ($controls as $control) {
                $control->clear($this->zibo);
            }
        }
    }

}