<?php

namespace zibo\library\mvc\view;

/**
 * A view represents the content which is sent back to the client
 */
interface View {

    /**
     * Renders the output for this view
     * @param boolean $willReturnValue True to return the rendered view, false
     * to send it straight to the client
     * @return mixed Null when provided $willReturnValue is set to true, the
     * rendered output otherwise
     */
    public function render($willReturnValue = true);

}