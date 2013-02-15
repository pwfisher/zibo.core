<?php

namespace zibo\core\event\loader;

use zibo\core\event\loader\io\EventIO;
use zibo\core\event\EventManager;
use zibo\core\Zibo;

/**
 * Interface for a event loader
 */
class GenericEventLoader implements EventLoader {

    /**
     * I/O implementation
     * @var zibo\core\event\loader\io\EventIO
     */
    protected $io;

    /**
     * Instance of the dependency injector
     * @var zibo\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Loaded events
     * @var array
     */
    protected $events;

    /**
     * Register statuses of the events
     * @var array
     */
    protected $registered;

    /**
     * Constructs a new event loader
     * @param zibo\core\event\loader\io\EventIO $io
     * @return null
     */
    public function __construct(EventIO $io, Zibo $zibo) {
        $this->io = $io;
        $this->zibo = $zibo;
        $this->events = false;
        $this->registered = array();
    }

    /**
     * Loads and registers the event listeners for the provided event
     * @param string $event Name of the event
     * @param EventManager $eventManager Instance of the event manager
     * @return null
     */
    public function loadEvents($event, EventManager $eventManager) {
        if (isset($this->registered[$event])) {
            return;
        }

        if ($this->events === false) {
            $this->events = $this->io->readEvents();
        }

        if (isset($this->events[$event])) {
            $this->registerEvents($event, $eventManager);
        }

        $this->registered[$event] = true;
    }

    /**
     * Registers the event listeners for the provided
     * @param unknown_type $event
     * @param EventManager $eventManager
     */
    protected function registerEvents($event, EventManager $eventManager) {
        foreach ($this->events[$event] as $e) {
            $callback = $this->processCallback($e->getCallback());
            $weight = $this->processParameter($e->getWeight());
            if ($weight) {
                $weight = (integer) $weight;
            }

            $eventManager->registerEventListener($event, $callback, $weight);
        }
    }

    /**
     * Processes the callback and creates the necessairy instances
     * @param string $callback Callback string
     * @return array|string
     */
    protected function processCallback($callback) {
        $callback = $this->processParameter($callback);

        if (strpos($callback, '->') !== false) {
            // instance method
            list($class, $method) = explode('->', $callback, 2);
            if (strpos($class, '#') === false) {
                $id = null;
            } else {
                list($class, $id) = explode('#', $class, 2);
            }

            $class = $this->processParameter($class);
            $id = $this->processParameter($id);
            $method = $this->processParameter($method);

            $instance = $this->zibo->getDependency($class, $id);

            return array($instance, $method);
        } elseif (strpos($callback, '::') !== false) {
            // static method
            list($class, $method) = explode('::', $callback, 2);

            $class = $this->processParameter($class);
            $method = $this->processParameter($method);

            return array($instance, $method);
        } else {
            // function
            return $callback;
        }
    }

    /**
     * Gets a parameter value if applicable (delimited by %)
     * @param string $parameter Parameter string
     * @return string Provided parameter if not a parameter string, the
     * parameter value otherwise
     */
    protected function processParameter($parameter) {
        if (substr($parameter, 0, 1) != '%' && substr($parameter, -1) != '%') {
            return $parameter;
        }

        $parameter = substr($parameter, 1, -1);

        if (strpos($parameter, '|') !== false) {
            list($key, $default) = explode('|', $parameter, 2);
        } else {
            $key = $parameter;
            $default = null;
        }

        return $this->zibo->getParameter($key, $default);
    }

}