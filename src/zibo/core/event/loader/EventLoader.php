<?php

namespace zibo\core\event\loader;

use zibo\core\event\EventManager;

/**
 * Interface for a event loader
 */
interface EventLoader {

    /**
     * Loads the event listeners for the provided event
     * @param string $event Name of the event
     * @param EventManager $eventManager Instance of the event manager
     * @return null
     */
    public function loadEvents($event, EventManager $eventManager);

}