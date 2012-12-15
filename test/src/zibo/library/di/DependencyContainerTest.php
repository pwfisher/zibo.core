<?php

namespace zibo\library\di;

use zibo\test\BaseTestCase;
use zibo\test\Reflection;

class DependencyContainerTest extends BaseTestCase {

    public function testConstruct() {
        $container = new DependencyContainer();

        $this->assertNotNull($container);
        $this->assertEquals(array(), Reflection::getProperty($container, 'dependencies'));
    }

    public function testAddDependency() {
        $container = new DependencyContainer();

        $for = 'foo';
        $className = 'className';
        $id = 'd0';
        $dependency = new Dependency($className);

        $container->addDependency($for, $dependency);
        $expected = array($for => array($id => $dependency));

        $this->assertEquals($expected, Reflection::getProperty($container, 'dependencies'));
        $this->assertEquals($id, $dependency->getId());

        $id = 'id';
        $dependency->setId($id);
        $container->addDependency($for, $dependency);
        $expected[$for][$id] = $dependency;

        $this->assertEquals($expected, Reflection::getProperty($container, 'dependencies'));

        $for = "bar";
        $container->addDependency($for, $dependency);
        $expected[$for][$id] = $dependency;

        $this->assertEquals($expected, Reflection::getProperty($container, 'dependencies'));

        $for = "foo";
        $dependency->setId();
        $container->addDependency($for, $dependency);
        $expected[$for]['d2'] = $dependency;

        $this->assertEquals($expected, Reflection::getProperty($container, 'dependencies'));
        $this->assertEquals('d2', $dependency->getId());
    }

    /**
     * @dataProvider providerAddDependencyThrowsExceptionWhenInvalidForProvided
     * @expectedException zibo\library\dependency\exception\DependencyException
     */
    public function testAddDependencyThrowsExceptionWhenInvalidForProvided($for) {
        $container = new DependencyContainer();
        $container->addDependency($for, new Dependency('className'));
    }

    public function providerAddDependencyThrowsExceptionWhenInvalidForProvided() {
        return array(
            array(''),
            array(null),
            array(array()),
            array($this),
        );
    }

}