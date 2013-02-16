<?php

namespace zibo\core\cache\control;

use zibo\core\event\loader\io\CachedEventIO;
use zibo\core\Zibo;

/**
 * Cache control implementation for the events
 */
class EventsCacheControl implements CacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'events';

    /**
     * Gets the name of this cache
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
     * Gets whether this cache can be toggled
     * @return boolean
     */
    public function canToggle() {
        return false;
    }

    /**
     * Gets whether this cache is enabled
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return boolean
     */
    public function isEnabled(Zibo $zibo) {
        $io = $zibo->getDependency('zibo\\core\\event\\loader\\io\\EventIO');

        return $io instanceof CachedEventIO;
    }

    /**
     * Enables this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function enable(Zibo $zibo) {

    }

    /**
     * Disable this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function disable(Zibo $zibo) {

    }

    /**
	 * Clears this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
	 * @return null
     */
    public function clear(Zibo $zibo) {
        $io = $zibo->getDependency('zibo\\core\\event\\loader\\io\\EventIO');
        if (!$io instanceof CachedEventIO) {
            return;
        }

        $file = $io->getFile();
        if ($file->exists()) {
            $file->delete();
        }
    }

}