<?php

namespace zibo\core\event\loader\io;

use zibo\core\environment\filebrowser\FileBrowser;
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
     * Instance of the file browser
     * @var zibo\core\environment\filebrowser\FileBrowser
     */
    private $fileBrowser;

    /**
     * Constructs a new event I/O
     * @param zibo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser) {
        $this->fileBrowser = $fileBrowser;
    }

    /**
     * Reads all the events from the data source
     * @return array Hierarchic array with the name of the event as key and an
     * array with Event instances as value
     */
    public function readEvents() {
        $events = array();

        $file = Zibo::DIRECTORY_CONFIG . File::DIRECTORY_SEPARATOR . self::FILE_NAME;

        $files = array_reverse($this->fileBrowser->getFiles($file));
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

            $events[] = new Event($event, $callback, $weight);
        }

        return $events;
    }

}