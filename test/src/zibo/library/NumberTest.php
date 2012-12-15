<?php

namespace zibo\library;

use zibo\test\BaseTestCase;

class NumberTest extends BaseTestCase {

    /**
     * @dataProvider providerIsNumeric
     */
    public function testIsNumeric($expected, $value) {
        $result = Number::isNumeric($value);
        $this->assertEquals($expected, $result);
    }

    public function providerIsNumeric() {
        return array(
            array(true, 7),
            array(true, -7),
            array(true, -7.33),
            array(true, '733'),
            array(true, '-7.33'),
            array(false, 'test'),
            array(false, array()),
            array(false, $this),
        );
    }

    /**
     * @dataProvider providerIsNumericNotNegative
     */
    public function testIsNumericNotNegative($expected, $value) {
        $result = Number::isNumeric($value, Number::NOT_NEGATIVE);
        $this->assertEquals($expected, $result);
    }

    public function providerIsNumericNotNegative() {
        return array(
            array(true, 0),
            array(true, 7),
            array(false, -1),
        );
    }

    /**
     * @dataProvider providerIsNumericNotZero
     */
    public function testIsNumericNotZero($expected, $value) {
        $result = Number::isNumeric($value, Number::NOT_ZERO);
        $this->assertEquals($expected, $result);
    }

    public function providerIsNumericNotZero() {
        return array(
            array(false, 0),
            array(true, 7),
            array(true, -1),
        );
    }

    /**
     * @dataProvider providerIsNumericNotFloat
     */
    public function testIsNumericNotFloat($expected, $value) {
        $result = Number::isNumeric($value, Number::NOT_FLOAT);
        $this->assertEquals($expected, $result);
    }

    public function providerIsNumericNotFloat() {
        return array(
            array(true, 0),
            array(true, 7),
            array(true, -1),
            array(false, 7.3),
            array(false, -7.3),
        );
    }

    /**
     * @dataProvider providerIsNumericNotNegativeNotZeroNotFloat
     */
    public function testIsNumericNotNegativeNotZeroNotFloat($expected, $value) {
        $result = Number::isNumeric($value, Number::NOT_NEGATIVE | Number::NOT_ZERO | Number::NOT_FLOAT);
        $this->assertEquals($expected, $result);
    }

    public function providerIsNumericNotNegativeNotZeroNotFloat() {
        return array(
            array(false, 0),
            array(true, 7),
            array(false, -1),
            array(false, 7.3),
            array(false, -7.3),
        );
    }

    /**
     * @dataProvider providerIsNumericOctal
     */
    public function testIsNumericOctal($expected, $value) {
        $result = Number::isNumeric($value, Number::OCTAL);
        $this->assertEquals($expected, $result);
    }

    public function providerIsNumericOctal() {
        return array(
            array(true, 0),
            array(true, 5),
            array(false, 9),
            array(false, 10923),
            array(true, 10723),
        );
    }

}