<?php

namespace zibo\library\mvc\dispatcher;

use zibo\library\http\Request as HttpRequest;
use zibo\library\mvc\Request;
use zibo\library\mvc\Response;
use zibo\library\router\Route;

use zibo\test\BaseTestCase;
use zibo\test\Reflection;

class GenericDispatcherTest extends BaseTestCase {

    private $dispatcher;

    protected function setUp() {
        $this->dispatcher = new GenericDispatcher();
    }

    public function testDispatch() {
        $actionName = 'testAction';
        $path = '/test';

        $controllerClass = 'zibo\\library\\mvc\\controller\\AbstractController';
        $controllerActions = array($actionName, 'setRequest', 'setResponse', 'preAction', 'postAction');
        $controllerMock = $this->getMock($controllerClass, $controllerActions);
        $controllerMockActionCall = $controllerMock->expects($this->once());
        $controllerMockActionCall->method($actionName);
        $controllerMockActionCall->will($this->returnValue(null));
        $controllerMockActionCall = $controllerMock->expects($this->once());
        $controllerMockActionCall->method('preAction');
        $controllerMockActionCall->will($this->returnValue(true));

        $route = new Route($path, array($controllerMock, $actionName));

        $request = new HttpRequest($path);
        $request = new Request($request, $route);
        $response = new Response();

        $result = $this->dispatcher->dispatch($request, $response);

        $this->assertNull($result);
    }

}