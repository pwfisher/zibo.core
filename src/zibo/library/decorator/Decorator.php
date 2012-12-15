<?php

namespace zibo\library\decorator;

/**
 * Interface to decorate/format a value for another context
 */
interface Decorator {

    /**
     * Decorate a value for another context
     * @param mixed $value
     * @return mixed decorated value
     */
    public function decorate($value);

}