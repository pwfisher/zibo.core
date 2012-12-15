<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\AutoCompletable;
use zibo\core\console\InputValue;

use zibo\library\config\Config;

/**
 * Abstract parameter command
 */
abstract class ParameterCommand extends AbstractCommand implements AutoCompletable {

    /**
     * Performs auto complete on the provided input
     * @param string $input The input value to auto complete
     * @return array|null Array with the auto completion matches or null when
     * no auto completion is available
     */
    public function autoComplete($input) {
        $completion = array();

        if (strpos($input, ' ') !== false) {
            return $completion;
        }

        $config = $this->zibo->getEnvironment()->getConfig();

        $values = $config->getAll();
        $values = Config::flattenConfig($values);

        foreach ($values as $key => $value) {
            if (strpos($key, $input) !== 0) {
                continue;
            }

            $completion[] = $key;
        }

        return $completion;
    }

}