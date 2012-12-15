<?php

namespace zibo\core\module;

use zibo\core\Zibo;

/**
 * Implementation of ModuleLoader to get the modules from the
 * dependency injector
 */
class DependencyModuleLoader implements ModuleLoader {

    /**
     * Full class name of the module interface
     * @var string
     */
    const INTERFACE_MODULE = 'zibo\\core\\module\\Module';

    /**
     * Loads the defined modules from the dependency injector
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return array Array with the defined modules
     * @see Module
     */
    public function loadModules(Zibo $zibo) {
        return $zibo->getDependencies(self::INTERFACE_MODULE);
    }

}