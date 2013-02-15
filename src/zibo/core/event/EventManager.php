<?php

namespace zibo\core\event;

use zibo\core\event\loader\EventLoader;
use zibo\core\Zibo;

use zibo\library\log\Log;
use zibo\library\Callback;

use \Exception;

/**
 * Manager of dynamic events
 */
class EventManager {

    /**
     * Default maximum number of event listeners for each event
     * @var int
     */
    const DEFAULT_MAX_EVENT_LISTENERS = 100;

    /**
     * Array with the event name as key and an array with callbacks to the
     * event listeners as value.
     * @var array
     */
    private $events;

    /**
     * Maximum number of event listeners for each event
     * @var int
     */
    private $maxEventListeners;

    /**
     * Default weight for a new event
     * @var int
     */
    private $defaultWeight;

    /**
     * Lazy event loader
     * @var zibo\core\event\loader\EventLoader
     */
    private $loader;

    /**
     * Instance of the Log
     * @var zibo\library\log\Log
     */
    private $log;

    /**
     * Constructs a new event manager
     * @param int $maxEventListeners Maximum number of event listeners for
     * each event
     * @return null
     */
    function __construct($maxEventListeners = null) {
        if ($maxEventListeners === null) {
            $maxEventListeners = self::DEFAULT_MAX_EVENT_LISTENERS;
        }

        $this->setMaxEventListeners($maxEventListeners);
        $this->events = array();
        $this->loader = null;
        $this->log = null;
    }

    /**
     * Sets the event loader
     * @param zibo\core\event\loader\EventLoader $loader
     * @return null
     */
    public function setLoader(EventLoader $loader) {
        $this->loader = $loader;
    }

    /**
     * Sets the Log
     * @param zibo\library\log\Log $log
     */
    public function setLog(Log $log = null) {
        $this->log = $log;
    }

    /**
     * Sets the maximum number of event listeners for each event
     * @param int $maxEventListeners
     * @return null
     * @throws Exception when the provided maxEventListeners is not
     * a positive number
     */
    private function setMaxEventListeners($maxEventListeners) {
        if (!is_integer($maxEventListeners) || $maxEventListeners <= 0) {
            throw new Exception('Provided maximum of events is zero or negative');
        }

        $this->maxEventListeners = $maxEventListeners;
        $this->defaultWeight = (int) floor($maxEventListeners / 2);
    }

    /**
     * Clears the event listeners for the provided event
     * @param string $event Name of the event, if not provided, all events will
     * be cleared
     * @return null
     * @throws Exception when the provided event name is empty or invalid
     */
    public function clearEventListeners($event = null) {
        if ($event === null) {
            $this->events = array();
            return;
        }

        $this->checkEventName($event);

        if (isset($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    /**
     * Registers a new event listener
     * @param string $event Name of the event
     * @param string|array|zibo\library\Callback $callback Callback for the
     * event listener
     * @param int $weight Weight for the new listener in the event listener
     * list. This will influence the order of the event listener calls. An
     * event with weight 1 will be called before an event with weight 10.
     * By default, a weight in the middle of the maximum amount will be used.
     * @return null
     * @throws Exception when the provided event name is empty or invalid
     * @throws Exception when the weight of the event listener is invalid
     * or already set
     */
    public function registerEventListener($event, $callback, $weight = null) {
        $this->checkEventName($event);

        // validate the weight value
        if ($weight === null) {
            $weight = $this->getNewWeight($event);
        } elseif (!is_integer($weight) || ($weight <= 0 && $weight >= $this->maxEventListeners)) {
            throw new Exception('Provided weight is invalid. Try a value between 0 and ' . $this->maxEventListeners);
        }

        // check the occupation
        if (isset($this->events[$event][$weight])) {
            throw new Exception('Weight ' . $weight . ' for event ' . $event . ' is already set with callback ' . $this->events[$event][$weight]);
        }

        // add it
        if (!isset($this->events[$event])) {
            $this->events[$event] = array();
        }

        $this->events[$event][$weight] = new Callback($callback);

        if ($this->log) {
            $this->log->logDebug('Registered listener for ' . $event, '#' . $weight . ' ' . $this->events[$event][$weight] . '()', Zibo::LOG_SOURCE);
        }

        // resort the event listeners by weight
        ksort($this->events[$event]);
    }

    /**
     * Gets the new weight for the provided event
     * @param string $event Name of the event
     * @return int The weight for a new event listener
     * @throws Exception when no weight could be found for the provided event
     */
    protected function getNewWeight($event) {
        $weight = $this->defaultWeight;

        do {
            if (!isset($this->events[$event][$weight])) {
                return $weight;
            }

            $weight++;
        } while ($weight < $this->maxEventListeners);

        throw new Exception('No new weight found for event ' . $event . '. Tried from ' . $this->defaultWeight . ' to ' . ($this->maxEventListeners - 1));
    }

    /**
     * Checks if there are event listeners registered for the provided event
     * @param string $event Name of the event
     * @return boolean
     * @throws Exception when the provided event name is empty or invalid
     */
    public function hasEventListeners($event) {
        $this->checkEventName($event);

        return isset($this->events[$event]);
    }

    /**
     * Triggers the listeners of the provided event. All arguments passed after
     * the event name are passed through to the event listener.
     * @param string $event Name of the event
     * @return null
     * @throws Exception when the provided event name is empty or invalid
     */
    public function triggerEvent($event) {
        if (!$this->checkEvent($event)) {
            return;
        }

        $arguments = func_get_args();
        unset($arguments[0]);

        $this->invokeEventListeners($event, $arguments);
    }

    /**
     * Triggers the listeners of the provided event with the provided arguments.
     * @param string $event Name of the event
     * @param array $arguments Array with the arguments for the event listener callbacks
     * @return null
     * @throws Exception when the provided event name is empty or invalid
     */
    public function triggerEventWithArrayArguments($event, array $arguments) {
        if (!$this->checkEvent($event)) {
            return;
        }

        $this->invokeEventListeners($event, $arguments);
    }

    /**
     * Invokes the event listeners for the provided event
     * @param string $event Name of the event
     * @param array $arguments Array with the arguments for the event listeners
     * @return null
     */
    private function invokeEventListeners($event, array $arguments) {
        if ($this->log) {
            $argumentsString = '';

            foreach ($arguments as $argument) {
                $argumentString = 'null';

                switch (gettype($argument)) {
                    case 'object':
                        $argumentString = get_class($argument);
                        break;
                    case 'string':
                        $argumentString = '"' . $argument . '"';
                        break;
                    default:
                        $argumentString = $argument;
                        break;
                }

                $argumentsString .= ($argumentsString ? ', ' : '') . $argumentString;
            }

            $argumentsString = '(' . $argumentsString . ')';
        }

        foreach ($this->events[$event] as $index => $callback) {
            if ($this->log) {
                $this->log->logDebug('Event ' . $event, ' #' . $index . ' ' . $callback . $argumentsString, Zibo::LOG_SOURCE);
            }

            $callback->invokeWithArrayArguments($arguments);
        }
    }

    /**
     * Checks if the provided event is invokable.
     * @param string $event Name of the event
     * @return boolean True if the event has listeners registered, false otherwise
     * @throws Exception when the provided event name is empty or invalid
     */
    protected function checkEvent($event) {
        $this->checkEventName($event);

        if ($this->loader) {
            $this->loader->loadEvents($event, $this);
        }

        if (!isset($this->events[$event])) {
            if ($this->log) {
                $this->log->logDebug('Event ' . $event, 'no listener registered', Zibo::LOG_SOURCE);
            }

            return false;
        }

        return true;
    }

    /**
     * Checks if the provided event name is valid
     * @param string $name Name of a event
     * @return null
     * @throws Exception when the provided event name is empty or invalid
     */
    protected function checkEventName($name) {
        if (!is_string($name) || $name == '') {
            throw new Exception('Provided name of the event is invalid or empty');
        }
    }

}