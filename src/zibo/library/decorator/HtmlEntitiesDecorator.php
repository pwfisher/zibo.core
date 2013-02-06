<?php

namespace zibo\library\decorator;

/**
 * Decorator to translate specials chars to html entities
 */
class HtmlEntitiesDecorator implements Decorator {

    /**
     * Gets the value to decorate, passes it through the decorateValue method
     * @param mixed $value Value to decorate
     * @return mixed Decorated value
     */
    public function decorate($value) {
        if (!is_string($value)) {
            return $value;
        }

        return htmlentities($value, ENT_NOQUOTES);
    }

}