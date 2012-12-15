<?php

namespace zibo\core\cache\control;

use zibo\core\Zibo;

/**
* Interface to control a cache
*/
interface CacheControl {

    /**
     * Gets the name of this cache
     * @return string
     */
    public function getName();

    /**
     * Gets whether this cache is enabled
	 * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return boolean
     */
    public function isEnabled(Zibo $zibo);

    /**
     * Enables this cache
	 * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function enable(Zibo $zibo);

    /**
     * Disables this cache
	 * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function disable(Zibo $zibo);

    /**
	 * Clears this cache
	 * @param zibo\core\Zibo $zibo Instance of Zibo
	 * @return null
     */
    public function clear(Zibo $zibo);

}