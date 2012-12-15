<?php

namespace zibo\core\mvc\controller;

use zibo\core\Zibo;

use zibo\library\mvc\controller\Controller;

/**
 * Interface for a controller of an action
 */
interface ZiboController extends Controller {

    /**
     * Sets the instance of Zibo to this controller
     * @param zibo\core\Zibo $zibo The instance of Zibo
     * @return null
     */
    public function setZibo(Zibo $zibo);

    /**
     * Gets the instance of Zibo from this controller
     * @return zibo\core\Zibo $zibo The instance of Zibo
     */
    public function getZibo();

}