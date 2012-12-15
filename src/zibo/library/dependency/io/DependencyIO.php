<?php

namespace zibo\library\dependency\io;

/**
 * Interface to get a dependency container
 */
interface DependencyIO {

    /**
     * Gets a dependency container
     * @return zibo\library\dependency\DependencyContainer
     */
    public function getContainer();

}