<?php

namespace zibo\core\event\loader\io;

/**
 * Interface to read event definitions from the data source
 */
interface EventIO {

    /**
     * Reads all the events from the data source
     * @return array Hierarchic array with the name of the event as key and an
     * array with Event instances as value
     */
    public function readEvents();

}