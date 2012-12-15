<?php

namespace zibo\library;

use zibo\test\BaseTestCase;
use zibo\test\Reflection;

use \Exception;

class ObjectFactoryTest extends BaseTestCase {

    /**
     * @var ObjectFactory
     */
    private $factory;

    protected function setUp() {
        $this->factory = new ObjectFactory();
    }

    public function testCreate() {
        $object = $this->factory->create('zibo\\library\\ObjectFactory');
        $this->assertNotNull($object, 'Result is null');
        $this->assertTrue($object instanceof ObjectFactory, 'Result is not an instance of the requested class');
    }

    public function testCreateThrowsExceptionWhenProvidedClassDoesNotExtendsNeededClass() {
        try {
            $this->factory->create('zibo\\library\\ObjectFactory', 'zibo\\library\\String');
        } catch (Exception $e) {
            return;
        }
        $this->fail();
    }

    public function testCreateThrowsExceptionWhenProvidedClassDoesNotImplementNeededClass() {
        try {
            $this->factory->create('zibo\\library\\ObjectFactory', 'zibo\\core\\Controller');
        } catch (Exception $e) {
            return;
        }
        $this->fail();
    }

    public function testCreateNonExistingClassThrowsException() {
        try {
            $this->factory->create('nonExistingClass');
            $this->fail('Exception expected for creating instance of non existing class');
        } catch (Exception $e) {
        }
    }

    /**
     * @dataProvider providerGetArguments
     */
    public function testGetArguments($expected, $class) {
        $arguments = $this->factory->getArguments($class);

        $this->assertEquals($expected, $arguments);
    }

    public function providerGetArguments() {
        return array(
            array(array(), 'zibo\library\ObjectFactory'),
            array(array('maxEventListeners' => null), 'zibo\library\EventManager'),
        );
    }

    /**
     * @dataProvider providerGetArgumentsThrowsExceptionWhenInvalidClassProvided
     */
    public function testGetArgumentsThrowsExceptionWhenInvalidClassProvided($class) {
        try {
            $this->factory->getArguments($class);
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function providerGetArgumentsThrowsExceptionWhenInvalidClassProvided() {
        return array(
            array(array()),
            array(null),
            array($this),
        );
    }

}