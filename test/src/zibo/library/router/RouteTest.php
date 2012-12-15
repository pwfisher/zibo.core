<?php

namespace zibo\library\router;

use zibo\test\BaseTestCase;

class RouteTest extends BaseTestCase {

    public function testConstruct() {
        $path = 'test/tester';
        $controllerClass = 'controller';
        $action = '*';

        $route = new Route($path, array($controllerClass, $action));

        $this->assertEquals($path, $route->getPath());
        $this->assertEquals($controllerClass . '::*', (string) $route->getCallback());
    }

    /**
     * @dataProvider providerConstructThrowsExceptionWhenInvalidPathProvided
     * @expectedException zibo\library\router\exception\RouterException
     */
    public function testConstructThrowsExceptionWhenInvalidPathProvided($path) {
        new Route($path, 'controller');
    }

    public function providerConstructThrowsExceptionWhenInvalidPathProvided() {
        return array(
           array(null),
           array('"!çà'),
        );
    }
}