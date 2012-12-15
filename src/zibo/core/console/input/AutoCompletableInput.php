<?php

namespace zibo\core\console\input;

use zibo\core\console\AutoCompletable;

/**
 * Interface for a auto completable input
 */
interface AutoCompletableInput extends Input {

    /**
     * Adds a auto completion implementation to the input
     * @param zibo\core\console\AutoCompletable $autoCompletable
     * @return null
     */
    public function addAutoCompletion(AutoCompletable $autoCompletable);

}