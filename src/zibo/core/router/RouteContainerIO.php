<?php

namespace zibo\core\router;

use zibo\library\router\RouteContainer;

/**
 * Interface to obtain the container for the router library
 */
interface RouteContainerIO {

    /**
     * Gets the route container from a data source
     * @return zibo\library\router\RouteContainer
     */
    public function getRouteContainer();

    /**
     * Sets the route container to the data source
     * @param zibo\library\router\RouteContainer;
     * @return null
     */
    public function setRouteContainer(RouteContainer $container);

}