<?php

namespace zibo\core\event\loader\io;

use zibo\core\event\loader\Event;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

use \Exception;

/**
 * Interface to read event definitions from the data source
 */
class ConfigEventIO implements EventIO {

    /**
     * File name
     * @var string
     */
    const FILE_NAME = 'events.conf';

    /**
     * Instance of Zibo
     * @var zibo\core\Zibo
     */
    private $zibo;

    /**
     * Constructs a new event I/O
     * @param zibo\core\Zibo $zibo
     * @return null
     */
    public function __construct(Zibo $zibo) {
        $this->zibo = $zibo;
    }

    /**
     * Reads all the events from the data source
     * @return array Hierarchic array with the name of the event as key and an
     * array with Event instances as value
     */
    public function readEvents() {
        $events = array();

        $environment = $this->zibo->getEnvironment()->getName();

        $file = Zibo::DIRECTORY_CONFIG . File::DIRECTORY_SEPARATOR . self::FILE_NAME;
        $environmentFile = Zibo::DIRECTORY_CONFIG . File::DIRECTORY_SEPARATOR . $environment . File::DIRECTORY_SEPARATOR . self::FILE_NAME;

        $files = array_reverse($this->zibo->getFiles($file)) + array_reverse($this->zibo->getFiles($environmentFile));
        foreach ($files as $file) {
            $fileEvents = $this->readEventsFromFile($file);
            foreach ($fileEvents as $fileEvent) {
                $event = $fileEvent->getEvent();

                if (!isset($events[$event])) {
                    $events[$event] = array($fileEvent);
                } else {
                    $events[$event][] = $fileEvent;
                }
            }
        }

        return $events;
    }

    /**
     * Reads the events file
     * @param zibo\library\filesystem\File $file File to read
     * @return array Array with Event objects
     * @throws Exception when a event line is invalid
     */
    public function readEventsFromFile(File $file) {
        $events = array();

        if ($file->isDirectory()) {
            throw new Exception('Provided file is a directory: ' . $file);
        }

        $content = $file->read();

        $lines = explode("\n", $content);
        foreach ($lines as $index => $originalLine) {
            $line = trim($originalLine);
            if (!$line) {
                continue;
            }

            $start = substr($line, 0, 1);
            if ($start == ';' || $start == '#') {
                continue;
            }

            $positionSpace = strpos($line, ' ');
            if ($positionSpace === false) {
                throw new Exception('Invalid event line in ' . $file . '(' . ($index+1) . '): no class set - ' . $originalLine);
            }

            $event = substr($line, 0, $positionSpace);

            $line = trim(substr($line, $positionSpace));

            $positionSpace = strpos($line, ' ');
            if ($positionSpace === false) {
                $callback = $line;
                $weight = null;
            } else {
                $callback = substr($line, 0, $positionSpace);
                $weight = trim(substr($line, $positionSpace));
            }

            $callback = $this->processCallback($callback);
            $weight = $this->processParameter($weight);

            $events[] = new Event($event, $callback, $weight);
        }

        return $events;
    }

    /**
     * Processes the parameters in the callback string
     * @param string $callback Callback string
     * @return string Provided callback with the parameters resolved
     */
    protected function processCallback($callback) {
        $callback = $this->processParameter($callback);

        if (strpos($callback, '->') !== false) {
            list($class, $method) = explode('->', $callback, 2);
            if (strpos($class, '#') === false) {
                $id = null;
            } else {
                list($class, $id) = explode('#', $class, 2);
            }

            $class = $this->processParameter($class);
            $id = $this->processParameter($id);
            $method = $this->processParameter($method);

            $callback = $class;
            if ($id) {
                $callback .= '#' . $id;
            }
            $callback .= '->' . $method;
        } elseif (strpos($callback, '::') !== false) {
            list($class, $method) = explode('::', $callback, 2);

            $class = $this->processParameter($class);
            $method = $this->processParameter($method);

            $callback = $class . '::' . $method;
        } else {
            $callback = $this->processParameter($callback);
        }

        return $callback;
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