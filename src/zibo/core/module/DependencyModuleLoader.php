<?php

namespace zibo\core\module;

use zibo\core\Zibo;

/**
 * Implementation of ModuleLoader to get the modules from the
 * dependency injector
 */
class DependencyModuleLoader implements ModuleLoader {

    /**
     * Loads the defined modules from the dependency injector
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return array Array with the defined modules
     * @see Module
     */
    public function loadModules(Zibo $zibo) {
        return $zibo->getDependencies('zibo\\core\\module\\Module');
    }

}