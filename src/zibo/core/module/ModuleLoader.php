<?php

namespace zibo\core\module;

use zibo\core\Zibo;

/**
 * Interface to load the module instances from a data source
 */
interface ModuleLoader {

    /**
     * Loads the defined Module objects
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return array Array with the defined modules
     * @see Module
     */
    public function loadModules(Zibo $zibo);

}