<?php

namespace zibo\core\cache\control;

use zibo\core\router\CachedRouteContainerIO;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

/**
 * Cache control implementation for the image cache
 */
class RouteCacheControl implements CacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'route';

    /**
     * Gets the name of this cache
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
    * Gets whether this cache is enabled
    * @param zibo\core\Zibo $zibo Instance of Zibo
    * @return boolean
    */
    public function isEnabled(Zibo $zibo) {
        $io = $zibo->getDependency('zibo\\core\\router\\RouteContainerIO');

        return $io instanceof CachedRouteContainerIO;
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
        $io = $zibo->getDependency('zibo\\core\\router\\RouteContainerIO');
        if (!$io instanceof CachedRouteContainerIO) {
            return;
        }

        $file  = $io->getFile();
        if ($file->exists()) {
            $file->delete();
        }
    }

}