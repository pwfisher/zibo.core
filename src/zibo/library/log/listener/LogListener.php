<?php

namespace zibo\library\log\listener;

use zibo\library\log\LogMessage;

/**
 * Interface for a listener of the Log
 */
interface LogListener {

    /**
     * Logs a message to this listener
     * @param zibo\library\log\LogMessage $message
     * @return null
     */
    public function logMessage(LogMessage $message);

}