<?php

namespace zibo\core\event\loader;

/**
 * Data container for a event definition
 */
class Event {

    /**
     * Name of the event
     * @var string
     */
    protected $event;

    /**
     * Callback of the event
     * @var zibo\library\Callback|array|string
     */
    protected $callback;

    /**
     * Weight of the event
     * @var integer
     */
    protected $weight;

    /**
     * Constructs a new event
     * @param string $event Name of the event
     * @param zibo\library\Callback|array|string $callback Callback to invoke
     * @param integer $weight Weight of the event
     * @return null
     */
    public function __construct($event, $callback, $weight = null) {
        $this->event = $event;
        $this->callback = $callback;
        $this->weight = $weight;
    }

    /**
     * Gets the name of the event
     * @return string
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * Gets the callback of the event
     * @return zibo\library\Callback|array|string
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * Gets the weight of the event
     * @return integer|null
     */
    public function getWeight() {
        return $this->weight;
    }

}