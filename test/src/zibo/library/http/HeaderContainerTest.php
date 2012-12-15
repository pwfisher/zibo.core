<?php

namespace zibo\library\http;

use zibo\test\BaseTestCase;
use zibo\test\Reflection;

class HeaderContainerTest extends BaseTestCase {

    private $hc;

    public function setUp() {
        $this->hc = new HeaderContainer();
    }

    public function testAddHeaderWithHeaderInstance() {
        $name = 'Name';
        $header = new Header($name, 'value');

        $this->assertEquals(array(), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->addHeader($header);

        $this->assertEquals(array($name => $header), Reflection::getProperty($this->hc, 'headers'));
    }

    public function testAddHeaderMultipleTimes() {
        $name = 'Name';
        $header = new Header($name, 'value');

        $this->assertEquals(array(), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->addHeader($header);
        $this->hc->addHeader($header);

        $this->assertEquals(array($name => array($header, $header)), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->addHeader($header);

        $this->assertEquals(array($name => array($header, $header, $header)), Reflection::getProperty($this->hc, 'headers'));
    }

    public function testAddHeaderWithNameAndValue() {
        $name = 'Name';
        $value = 'value';

        $this->assertEquals(array(), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->addHeader($name, $value);

        $this->assertEquals(array($name => new Header($name, $value)), Reflection::getProperty($this->hc, 'headers'));
    }

    /**
     * @dataProvider providerAddHeaderWithInvalidValuesThrowsException
     * @expectedException zibo\library\http\exception\HttpException
     */
    public function testAddHeaderWithInvalidValuesThrowsException($header, $value = null) {
        $this->hc->addHeader($header, $value);
    }

    public function providerAddHeaderWithInvalidValuesThrowsException() {
        return array(
            array(null),
            array(array()),
            array($this),
            array('name', null),
            array('name', array()),
            array('name', $this),
        );
    }

    public function testSetHeaderWithHeaderInstance() {
        $name = 'Name';
        $header = new Header($name, 'value');

        $this->hc->addHeader($header);
        $this->hc->addHeader($header);

        $this->assertEquals(array($name => array($header, $header)), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->setHeader($header);

        $this->assertEquals(array($name => $header), Reflection::getProperty($this->hc, 'headers'));
    }

    public function testSetHeaderWithNameAndValue() {
        $name = 'Name';
        $name2 = 'Name2';
        $value = 'value';
        $header = new Header($name, $value);
        $header2 = new Header($name2, $value);

        $this->hc->addHeader($header);
        $this->hc->addHeader($header);
        $this->hc->addHeader($header2);

        $this->assertEquals(array($name => array($header, $header), $name2 => $header2), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->setHeader($name, $value);

        $this->assertEquals(array($name => $header, $name2 => $header2), Reflection::getProperty($this->hc, 'headers'));
    }

    /**
    * @dataProvider providerAddHeaderWithInvalidValuesThrowsException
    * @expectedException zibo\library\http\exception\HttpException
    */
    public function testSetHeaderWithInvalidValuesThrowsException($header, $value = null) {
        $this->hc->setHeader($header, $value);
    }

    /**
     * @dataProvider providerHasHeader
     */
    public function testHasHeader($expected, $header) {
        $this->hc->addHeader('Content-Length', 123);
        $this->hc->addHeader('Accept-Language', 'en');

        $result = $this->hc->hasHeader($header);

        $this->assertEquals($expected, $result);
    }

    public function providerHasHeader() {
        return array(
            array(true, 'Content-Length'),
            array(true, 'content-length'),
            array(false, 'content'),
        );
    }

    /**
     * @dataProvider providerHasHeaderThrowsExceptionWhenInvalidNameProvided
     * @expectedException zibo\library\http\exception\HttpException
     */
    public function testHasHeaderThrowsExceptionWhenInvalidNameProvided($name) {
        $this->hc->hasHeader($name);
    }

    public function providerHasHeaderThrowsExceptionWhenInvalidNameProvided() {
        return array(
            array(null),
            array(array()),
            array($this),
        );
    }

    public function testGetHeader() {
        $name = 'Name';
        $value = 'value';

        $this->hc->addHeader($name, $value);

        $header = $this->hc->getHeader($name);

        $this->assertEquals($name, $header->getName());
        $this->assertEquals($value, $header->getValue());

        $this->hc->addHeader($header);

        $headers = $this->hc->getHeader($name);

        $this->assertTrue(is_array($headers));
        $this->assertEquals(array($header, $header), $headers);

        $header = $this->hc->getHeader('Unexistant');

        $this->assertNull($header);
    }

    /**
     * @dataProvider providerGetHeaderThrowsExceptionWhenInvalidHeaderProvided
     * @expectedException zibo\library\http\exception\HttpException
     */
    public function testGetHeaderThrowsExceptionWhenInvalidHeaderProvided($name) {
        $this->hc->getHeader($name);
    }

    public function providerGetHeaderThrowsExceptionWhenInvalidHeaderProvided() {
        return array(
            array(null),
            array(array()),
            array($this),
        );
    }

    public function testRemoveHeader() {
        $name = 'Name1';
        $name2 = 'Name2';
        $name3 = 'Name3';
        $header = new Header($name, 'value');
        $header2 = new Header($name2, 'value');
        $header3 = new Header($name3, 'value');

        $this->hc->addHeader($name, 'value');
        $this->hc->addHeader($header);
        $this->hc->addHeader($header2);

        $this->assertEquals(array($name => array($header, $header), $name2 => $header2), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->removeHeader($name);

        $this->assertEquals(array($name2 => $header2), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->addHeader($header);
        $this->hc->addHeader($header3);

        $this->assertEquals(array($name2 => $header2, $name => $header, $name3 => $header3), Reflection::getProperty($this->hc, 'headers'));

        $this->hc->removeHeader(array($name2, $name3));

        $this->assertEquals(array($name => $header), Reflection::getProperty($this->hc, 'headers'));
    }

    public function testCreateFromServer() {
        $_SERVER = array(
            'HTTP_HOST' => 'localhost',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_USER_AGENT' => 'Mozilla...',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'PATH' => '/usr/local/bin:/usr/bin:/bin',
            'SERVER_NAME' => 'localhost',
        );

        $expected = array(
            'Host' => new Header('Host', $_SERVER['HTTP_HOST']),
            'Connection' => new Header('Connection', $_SERVER['HTTP_CONNECTION']),
            'User-Agent' => new Header('User-Agent', $_SERVER['HTTP_USER_AGENT']),
            'Accept' => new Header('Accept', $_SERVER['HTTP_ACCEPT']),
        );

        $this->hc = HeaderContainer::createFromServer();

        $this->assertEquals($expected, Reflection::getProperty($this->hc, 'headers'));
    }

    public function testAddCacheControlDirective() {
        $this->assertEquals(array(), Reflection::getProperty($this->hc, 'cacheControl'));

        $this->hc->addCacheControlDirective('private');

        $this->assertEquals(array('private' => true), Reflection::getProperty($this->hc, 'cacheControl'));
        $this->assertEquals('private', $this->hc->getHeader('Cache-Control')->getValue());

        $this->hc->addCacheControlDirective('max-age', 60);

        $this->assertEquals(array('private' => true, 'max-age' => 60), Reflection::getProperty($this->hc, 'cacheControl'));
        $this->assertEquals('private, max-age=60', $this->hc->getHeader('Cache-Control')->getValue());
    }

    public function testGetCacheControlDirective() {
        $this->hc->addCacheControlDirective('no-store');
        $this->hc->addCacheControlDirective('max-age', 60);
        $this->hc->addCacheControlDirective('private', 'Vary');

        $this->assertEquals(true, $this->hc->getCacheControlDirective('no-store'));
        $this->assertEquals(60, $this->hc->getCacheControlDirective('max-age'));
        $this->assertEquals('Vary', $this->hc->getCacheControlDirective('private'));
    }

    public function testRemoveCacheControlDirective() {
        $this->assertEquals(array(), Reflection::getProperty($this->hc, 'cacheControl'));

        $this->hc->addCacheControlDirective('private');
        $this->hc->addCacheControlDirective('max-age', 60);

        $this->assertEquals(array('private' => true, 'max-age' => 60), Reflection::getProperty($this->hc, 'cacheControl'));

        $this->hc->removeCacheControlDirective('max-age');

        $this->assertEquals(array('private' => true), Reflection::getProperty($this->hc, 'cacheControl'));
        $this->assertEquals('private', $this->hc->getHeader('Cache-Control')->getValue());

        $this->hc->removeCacheControlDirective('private');

        $this->assertEquals(array(), Reflection::getProperty($this->hc, 'cacheControl'));
        $this->assertNull($this->hc->getHeader('Cache-Control'));
    }

    public function testAddCacheControlHeaderWillParseDirectives() {
        $this->hc->addHeader('Cache-Control', 'private,max-age=60,test="test value"');

        $this->assertEquals(true, $this->hc->getCacheControlDirective('private'));
        $this->assertEquals(60, $this->hc->getCacheControlDirective('max-age'));
        $this->assertEquals('test value', $this->hc->getCacheControlDirective('test'));
    }

    public function testIterator() {
        $this->hc->addHeader('Content-Length', 123);
        $this->hc->addHeader('Set-Cookie', 'name=test');
        $this->hc->addHeader('Set-Cookie', 'flag=1');
        $this->hc->addHeader('Age', 100);

        $expected = "Content-Length: 123\nSet-Cookie: name=test\nSet-Cookie: flag=1\nAge: 100";

        $output = '';
        foreach($this->hc as $header) {
            $output .= ($output ? "\n" : "") . ((string) $header);
        }

        $this->assertEquals($expected, $output);
    }

    public function testCount() {
        $this->hc->addHeader('name', 'value');
        $this->hc->addHeader('name', 'value2');
        $this->hc->addHeader('name2', 'value3');

        $this->assertEquals(3, $this->hc->count());
    }

}