<?php

namespace zibo\library;

use zibo\test\BaseTestCase;

use \Exception;

class BooleanTest extends BaseTestCase {

    /**
     * @dataProvider providerGetBoolean
     */
    public function testGetBoolean($expected, $value, $message) {
        $this->assertEquals($expected, Boolean::getBoolean($value), $message);
    }

    public function providerGetBoolean() {
        return array(
           array(true, '1', 'string 1'),
           array(true, 1, 'number 1'),
           array(false, 0, 'number 0'),
           array(false, 'FALSE', 'string FALSE'),
           array(true, 'true', 'string true'),
           array(true, true, 'boolean true'),
        );
    }

    /**
     * @dataProvider providerGetBooleanThrowsExceptionWhenNonBooleanValueIsPassed
     */
    public function testGetBooleanThrowsExceptionWhenNonBooleanValueIsPassed($value) {
        try {
            Boolean::getBoolean($value);
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function providerGetBooleanThrowsExceptionWhenNonBooleanValueIsPassed() {
        return array(
            array('monkey'),
            array($this),
        );
    }

}