<?php

namespace zibo\library\mvc\view;

/**
 * View for a JSON response
 */
class JsonView implements View {

    /**
     * Value to be encoded to JSON
     * @var mixed
     */
    private $value;

    /**
     * Options for the json_encode function
     * @var integer
     */
    private $options;

    /**
     * Constructs a new JSON view
     * @param mixed $value Value to be encoded to JSON
     * @param integer $options Options for the json_encode function
     * @return null
     * @see json_encode
     */
    public function __construct($value, $options = 0) {
        $this->value = $value;
        $this->options = $options;
    }

    /**
     * Renders the output for this view by encoding the value into JSON
     * @param boolean $willReturnValue True to return the rendered view, false
     * to send it straight to the client
     * @return mixed Null when provided $willReturnValue is set to true, the
     * rendered output otherwise
     */
    public function render($willReturnValue = true) {
        $encoded = json_encode($this->value, $this->options);

        if ($willReturnValue) {
            return $encoded;
        }

        echo $encoded;
    }

}