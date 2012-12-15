<?php

namespace zibo\library\log\listener;

use zibo\library\log\LogMessage;

use \Exception;

/**
 * Log listener to echo log items
 */
class EchoLogListener extends AbstractLogListener {

    /**
     * Echos a log item
     * @param zibo\library\log\LogItem $item Item to echo
     * @return null
     */
    public function logMessage(LogMessage $message) {
        if (!$this->isLoggable($message->getLevel())) {
            return;
        }

        echo $this->getLogMessageAsString($message);
    }

}