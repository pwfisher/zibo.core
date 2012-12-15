<?php

namespace zibo\library;

use zibo\test\BaseTestCase;

use \Exception;

class PhpTest extends BaseTestCase {

    /**
     * @dataProvider providergetValueAsString
     */
    public function testgetValueAsString($expected, $value) {
        $this->assertEquals($expected, Php::getValueAsString($value));
    }

    public function providergetValueAsString() {
        return array(
            array('null', null),
            array('true', true),
            array('false', false),
            array(12, 12),
            array(12.3, 12.3),
            array('\'12\'', '12'),
            array('\'test\'', 'test'),
            array('\'It\\\'s cool\'', 'It\'s cool'),
            array('array()', array()),
            array("array(\n    0 => 'value1',\n    1 => 'value2',\n)", array('value1', 'value2')),
        );
    }

    /**
     * @dataProvider providergetValueAsStringThrowsExceptionWhenUnsupportedValueProvided
     */
    public function testgetValueAsStringThrowsExceptionWhenUnsupportedValueProvided($value) {
        try {
            Php::getValueAsString($value);
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function providergetValueAsStringThrowsExceptionWhenUnsupportedValueProvided() {
        return array(
            array($this), // object
            array(STDIN), // resource
        );
    }

}