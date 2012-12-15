<?php

namespace zibo\core\module;

use zibo\core\Zibo;

/**
 * Interface to connect a module to Zibo
 */
interface Module {

    /**
     * Boots your module, used for initialization, event registration, ...
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function boot(Zibo $zibo);

}