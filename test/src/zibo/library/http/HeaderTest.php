<?php

namespace zibo\library\http;

use zibo\test\BaseTestCase;

class HeaderTest extends BaseTestCase {

    public function testConstruct() {
        $name = 'Name';
        $value = 'value';

        $header = new Header($name, $value);

        $this->assertEquals($name, $header->getName());
        $this->assertEquals($value, $header->getValue());
    }

    /**
     * @dataProvider providerConstructWithInvalidValuesThrowsException
     * @expectedException zibo\library\http\exception\HttpException
     */
    public function testConstructWithInvalidValuesThrowsException($name, $value) {
        new Header($name, $value);
    }

    public function providerConstructWithInvalidValuesThrowsException() {
        return array(
            array('', 'value'),
            array(null, 'value'),
            array(array(), 'value'),
            array($this, 'value'),
            array('name', null),
            array('name', array()),
            array('name', $this),
        );
    }

    /**
     * @dataProvider providerEquals
     */
    public function testEquals($expected, $test) {
        $name = 'Name';
        $value = 'value';

        $header = new Header($name, $value);
        $result = $header->equals($test);

        $this->assertEquals($expected, $result);
    }

    public function providerEquals() {
        return array(
            array(true, new Header('Name', 'value')),
            array(false, new Header('Name', 'value2')),
            array(false, new Header('Name2', 'value')),
            array(false, 'value'),
            array(false, null),
            array(false, array()),
            array(false, $this),
        );
    }

    /**
     * @dataProvider providerParseName
     */
    public function testParseName($expected, $name) {
        $name = Header::parseName($name);

        $this->assertEquals($expected, $name);
    }

    public function providerParseName() {
        return array(
            array('Name', 'name'),
            array('Content-Length', 'content-length'),
            array('Content-Length', 'CONTENT_LENGTH'),
        );
    }

    /**
     * @dataProvider providerParseTime
     */
    public function testParseTime($expected, $value) {
        $time = Header::parseTime($value);

        $this->assertEquals($expected, $time);
    }

    public function providerParseTime() {
        return array(
            array('Wed, 01 Dec 2010 16:00:00 GMT', 1291219200),
            array(1291219200, 'Thu, 01 Dec 2010 16:00:00 GMT'),
        );
    }

    /**
     * @dataProvider providerParseAccept
     */
    public function testParseAccept($expected, $value) {
        $result = Header::parseAccept($value);

        $this->assertEquals($expected, $result);
    }

    public function providerParseAccept() {
        return array(
            array(
                array(
                	'text/x-c' => 1,
                	'text/html' => 1,
                	'text/x-dvi' => 0.8,
            		'text/plain' => 0.5,
                ),
                'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c',
            ),
            array(
                array(
                    'ISO-8859-1' => 1,
                    '*' => 0.7,
                    'utf-8' => 0.7,
                ),
        		'ISO-8859-1,utf-8;q=0.7,*;q=0.7'
            ),
            array(
                array(
                    'gzip' => 1,
                    'identity' => 0.5,
                ),
        		'gzip;q=1.0, identity; q=0.5, *;q=0'
            ),
            array(
                array(
                    'da' => 1,
                    'en-gb' => 0.8,
                    'en' => 0.7,
                ),
        		'da, en-gb;q=0.8, en;q=0.7'
            ),
        );
    }

    /**
     * @dataProvider providerParseIfMatch
     */
    public function testParseIfMatch($expected, $value) {
        $result = Header::parseIfMatch($value);

        $this->assertEquals($expected, $result);
    }

    public function providerParseIfMatch() {
        return array(
            array(array('abc' => false), 'abc'),
            array(array('abc' => false), '"abc"'),
            array(array('abc' => true), 'W/"abc"'),
            array(array('abc' => true, 'def' => false, 'ghi' => true), 'W/"abc", "def",W/"ghi"'),
        );
    }

}