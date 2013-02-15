<?php

namespace zibo\core;

use zibo\core\environment\dependency\argument\CallArgumentParser;
use zibo\core\environment\dependency\argument\ConfigArgumentParser;
use zibo\core\environment\dependency\argument\DependencyArgumentParser;
use zibo\core\environment\Environment;
use zibo\core\mvc\view\FileView;
use zibo\core\mvc\Request;
use zibo\core\mvc\Response;

use zibo\library\dependency\DependencyContainer;
use zibo\library\dependency\DependencyInjector;
use zibo\library\http\Cookie;
use zibo\library\http\Header;
use zibo\library\http\Request as HttpRequest;
use zibo\library\log\Log;
use zibo\library\mvc\dispatcher\Dispatcher;
use zibo\library\router\exception\RouterException;
use zibo\library\router\Route;
use zibo\library\router\Router;
use zibo\library\ObjectFactory;

use \Exception;

/**
 * Kernel of the Zibo framework
 */
class Zibo {

    /**
     * The version of Zibo
     * @var string
     */
    const VERSION = '0.12.0';

    /**
     * Full class name of the default controller
     * @var string
     */
    const DEFAULT_CONTROLLER = 'zibo\\library\\mvc\\controller\\IndexController';

    /**
     * Default session timeout time in seconds
     * @var integer
     */
    const DEFAULT_SESSION_TIMEOUT = 1800;

    /**
     * Name of the cache directory
     * @var string
     */
    const DIRECTORY_CACHE = 'cache';

    /**
     * Name of the config directory
     * @var string
     */
    const DIRECTORY_CONFIG = 'config';

    /**
     * Name of the data directory
     * @var string
     */
    const DIRECTORY_DATA = 'data';

    /**
     * Name of localization directory
     * @var string
     */
    const DIRECTORY_L10N = 'l10n';

    /**
     * Name of the manual directory
     * @var string
     */
    const DIRECTORY_MANUAL = 'manual';

    /**
     * Name of the public directory
     * @var string
     */
    const DIRECTORY_PUBLIC = 'public';

    /**
     * Name of the source directory
     * @var string
     */
    const DIRECTORY_SOURCE = 'src';

    /**
     * Name of the test directory
     * @var string
     */
    const DIRECTORY_TEST = 'test';

    /**
     * Name of the vendor directory
     * @var string
     */
    const DIRECTORY_VENDOR = 'vendor';

    /**
     * Name of the view directory
     * @var string
     */
    const DIRECTORY_VIEW = 'view';

    /**
     * Name of the event run when an exception occurs
     * @var string
     */
    const EVENT_EXCEPTION = 'core.exception';

    /**
     * Name of the event which is run before routing
     * @var string
     */
    const EVENT_PRE_ROUTE = 'core.route.pre';

    /**
     * Name of the event which is run after routing
     * @var string
     */
    const EVENT_POST_ROUTE = 'core.route.post';

    /**
     * Name of the event which is run before dispatching
     * @var string
     */
    const EVENT_PRE_DISPATCH = 'core.dispatch.pre';

    /**
     * Name of the event which is run after dispatching
     * @var string
     */
    const EVENT_POST_DISPATCH = 'core.dispatch.post';

    /**
     * Name of the event which is run before sending the response
     * @var string
     */
    const EVENT_PRE_RESPONSE = 'core.response.pre';

    /**
     * Name of the event which is run after sending the response
     * @var string
     */
    const EVENT_POST_RESPONSE = 'core.response.post';

    /**
     * The source name of the log messages
     * @var string
     */
    const LOG_SOURCE = 'core';

    /**
     * Parameter for the secret key of the system
     * @var string
     */
    const PARAM_SECRET = "system.secret";

    /**
     * Parameter for the session timeout in seconds
     * @var string
     */
    const PARAM_SESSION_TIMEOUT = 'system.session.timeout';

    /**
     * Idle state when the Zibo is not working
     * @var string
     */
    const STATE_IDLE = 'idle';

    /**
     * State value when booting Zibo
     * @var string
     */
    const STATE_BOOT = 'boot';

    /**
     * State value when routing a request
     * @var string
     */
    const STATE_ROUTE = 'route';

    /**
     * State value when sending the response
     * @var string
     */
    const STATE_DISPATCH = 'dispatch';

    /**
     * State value when sending the response
     * @var string
     */
    const STATE_RESPONSE = 'response';

    /**
     * The environment we are working in
     * @var zibo\core\environment\Environment
     */
    protected $environment;

    /**
     * Instance of the Log
     * @var zibo\library\log\Log
     */
    protected $log;

    /**
     * Instance of the dependency injector
     * @var zibo\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Data container of the request
     * @var Request
     */
    protected $request;

    /**
     * Instance of the response
     * @var Response
     */
    protected $response;

    /**
     * Router to obtain the Request object
     * @var zibo\library\router\Router
     */
    protected $router;

    /**
     * Dispatcher of the actions in the controllers
     * @var zibo\library\mvc\dispatcher\Dispatcher
     */
    protected $dispatcher;

    /**
     * The current state of Zibo
     * @var string
     */
    protected $state;

    /**
     * Constructs a new instance of the Zibo kernel
     * @param zibo\core\environment\Environment The environment to use
     * @return null
     */
    public function __construct(Environment $environment) {
        $this->environment = $environment;

        $this->setDependencyInjector();

        try {
            $this->log = $this->getDependency('zibo\\library\\log\\Log');
        } catch (Exception $e) {

        }

        $this->eventManager = $this->getDependency('zibo\\core\\event\\EventManager');

        $this->request = null;
        $this->response = null;
        $this->router = null;
        $this->dispatcher = null;

        $this->state = self::STATE_IDLE;
    }

    /**
     * Gets the current state of Zibo
     * @return string State constant
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Gets the environment we're living in
     * @return zibo\core\environment\Environment
     */
    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * Gets the Log
     * @return zibo\library\log\Log
     */
    public function getLog() {
        return $this->log;
    }

    /**
     * Gets the public directory
     * @return zibo\library\filesystem\File
     */
    public function getPublicDirectory() {
        return $this->environment->getFileBrowser()->getPublicDirectory();
    }

    /**
     * Gets the application directory
     * @return zibo\library\filesystem\File
     */
    public function getApplicationDirectory() {
        return $this->environment->getFileBrowser()->getApplicationDirectory();
    }

    /**
     * Gets a file from the public directory structure
     * @param string $file Relative path to the public directory structure
     * @return zibo\library\filesystem\File|null Instance of the file if found,
     * null otherwise
     */
    public function getPublicFile($file) {
        return $this->environment->getFileBrowser()->getPublicFile($file);
    }

    /**
     * Gets a file from the file system structure
     * @param string $file File name relative to the file system structure
     * @return zibo\library\filesystem\File|null Instance of the file if found,
     * null otherwise
     */
    public function getFile($file) {
        return $this->environment->getFileBrowser()->getFile($file);
    }

    /**
     * Gets multiple files from the file system structure
     * @param string $file File name relative to the file system structure
     * @return array Array with File objects which have the provided name
     */
    public function getFiles($file) {
        return $this->environment->getFileBrowser()->getFiles($file);
    }

    /**
     * Gets the relative file in the Zibo file structure for a given path.
     * @param string|zibo\library\filesystem\File $file File to get the
     * relative file from
     * @param boolean $public Set to true to check the public directory as well
     * @return zibo\library\filesystem\File relative file in the Zibo file
     * structure if located in the root of the Zibo installation
     * @throws zibo\library\filesystem\exception\FilesystemException when the
     * provided file is not in the root path
     * @throws zibo\library\filesystem\exception\FileSystemException when the
     * provided file is part of the Zibo file system structure
     */
    public function getRelativeFile($file, $public = false) {
        return $this->environment->getFileBrowser()->getRelativeFile($file, $public);
    }

    /**
     * Sets a parameter value to the environment
     * @param string $key
     * @param mixed $value
     * @return null
     */
    public function setParameter($key, $value) {
        $this->environment->getConfig()->set($key, $value);
    }

    /**
     * Gets a parameter of the environment
     * @param string $key Key of the parameter
     * @param mixed $default Default value for when the parameter is not set
     * @return mixed The parameter value or the provided default value
     * when the parameter is not set
     */
    public function getParameter($key, $default = null) {
        return $this->environment->getConfig()->get($key, $default);
    }

    /**
     * Gets the secret key of the system, usuable for encryption
     * @return string
     */
    public function getSecretKey() {
        $secret = $this->getParameter(self::PARAM_SECRET);

        if (!$secret) {
            $secret = substr(hash('sha512', md5(time())), 0, 21);

            $this->setParameter(self::PARAM_SECRET, $secret);
        }

        return $secret;
    }

    /**
     * Initializes the dependency injector
     * @param zibo\library\dependency\DependencyContainer $container
     * @return null
     */
    protected function setDependencyInjector() {
        $config = $this->environment->getConfig();
        $callArgumentParser = new CallArgumentParser($config);
        $configArgumentParser = new ConfigArgumentParser($config);
        $dependencyArgumentParser = new DependencyArgumentParser($config);

        $container = $this->environment->getDependencyContainer();
        $objectFactory = new ObjectFactory();

        $this->dependencyInjector = new DependencyInjector($container, $objectFactory);
        $this->dependencyInjector->setArgumentParser(DependencyInjector::TYPE_CALL, $callArgumentParser);
        $this->dependencyInjector->setArgumentParser(DependencyInjector::TYPE_DEPENDENCY, $dependencyArgumentParser);
        $this->dependencyInjector->setArgumentParser('parameter', $configArgumentParser);

        $this->dependencyInjector->setInstance($objectFactory);
        $this->dependencyInjector->setInstance($this);
        $this->dependencyInjector->setInstance($this->dependencyInjector);
        $this->dependencyInjector->setInstance($this->environment);
        $this->dependencyInjector->setInstance($this->environment->getConfig());
        $this->dependencyInjector->setInstance($this->environment->getFileBrowser(), 'zibo\\core\\environment\\filebrowser\\FileBrowser');
    }

    /**
     * Gets the instance of the dependency injector
     * @return zibo\library\dependency\DependencyInjector
     */
    public function getDependencyInjector() {
        return $this->dependencyInjector;
    }

    /**
     * Gets a instance of a class through dependency injection
     * @param string $interface Full class name of the interface or parent class
     * @param string $id Set if a specific instance is required
     * @param array $arguments Arguments for the constructor of the instance.
     * Passing arguments will always result in a new instance. Omitted
     * arguments will be
     * @return mixed The instance of the requested interface
     * @throws zibo\library\dependency\exceptin\DependencyException when the interface
     * is not set or could not be loaded
     */
    public function getDependency($interface, $id = null, array $arguments = null) {
        if ($this->dependencyInjector === null) {
            $this->setDependencyInjector();
        }

        return $this->dependencyInjector->get($interface, $id, $arguments);
    }

    /**
     * Gets all the instances of a class through dependency injection
     * @param string $interface Full class name of the interface or parent class
     * @return array Array with the id of the dependency as key and the instance
     * as value
     * @throws zibo\library\dependency\exceptin\DependencyException when the interface
     * is not set or could not be loaded
     */
    public function getDependencies($interface) {
        if ($this->dependencyInjector === null) {
            $this->setDependencyInjector();
        }

        return $this->dependencyInjector->getAll($interface);
    }

    /**
     * Registers a event listener for an event
     * @param string $eventName name of the event
     * @param mixed $callback listener callback
     * @param int $weight weight of the listener to influence the order of listeners
     * @return null
     */
    public function registerEventListener($eventName, $callback, $weight = null) {
        $this->eventManager->registerEventListener($eventName, $callback, $weight);
    }

    /**
     * Clears the listeners.
     *
     * Removes all the event listeners for the provided event. If no event is
     * provided, all event listeners will be cleared.
     * @param string $eventName Name of the event
     * @return null
     */
    public function clearEventListeners($eventName = null) {
        $this->eventManager->clearEventListeners($eventName);
    }

    /**
     * Triggers an event.
     *
     * The instance of Zibo will always be passed as the first parameter to the
     * event listeners. All parameters passed after the event name of this
     * method call, will be passed through to the event listeners after the
     * Zibo instance.
     * @param string $eventName Name of the event
     * @return null
     * @throws zibo\ZiboException when trying to run a system event
     */
    public function triggerEvent($eventName) {
        $isEventAllowed = strlen($eventName) >= 5 && substr($eventName, 0, 5) == 'core.';
        if ($isEventAllowed) {
            throw new Exception('Can\'t run core events from outside the Zibo kernel.');
        }

        $arguments = func_get_args();
        $arguments[0] = $this;

        $this->eventManager->triggerEventWithArrayArguments($eventName, $arguments);
    }

    /**
     * Loads the modules and invokes the boot method on them
     * @return null
     */
    public function bootModules() {
        $this->state = self::STATE_BOOT;

        $moduleLoader = $this->getDependency('zibo\\core\\module\\ModuleLoader');

        $modules = $moduleLoader->loadModules($this);
        foreach ($modules as $module) {
            if ($this->log) {
                $this->log->logDebug('Booting module ' . get_class($module), null, self::LOG_SOURCE);
            }

            $module->boot($this);
        }

        $this->state = self::STATE_IDLE;
    }

    /**
     * Gets the response
     * @return Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Sets the request
     * @param Request $request
     * @return null
     */
    public function setRequest(Request $request = null) {
        $this->request = $request;
    }

    /**
     * Gets the request
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Sets the router
     * @param Router $router
     * @return null
     */
    public function setRouter(Router $router) {
        $this->router = $router;
    }

	/**
     * Gets the router
     * @return Router
     */
    public function getRouter() {
        if (!$this->router) {
            $this->router = $this->getDependency('zibo\\library\\router\\Router');
        }

        return $this->router;
    }

    /**
     * Gets the URL of the provided route
     * @param string $routeId The id of the route
     * @param array $arguments Path arguments for the route
     * @return string
     * @throws zibo\library\router\exception\RouterException If the route is
     * not found
     */
    public function getUrl($routeId, array $arguments = null) {
        $routeContainer = $this->getRouter()->getRouteContainer();

        $route = $routeContainer->getRouteById($routeId);
        if (!$route) {
            throw new RouterException('No route found with id ' . $routeId);
        }

        return $route->getUrl($this->request->getBaseScript(), $arguments);
    }

    /**
     * Sets the dispatcher
     * @param zibo\library\mvc\dispatcher\Dispatcher $dispatcher
     * @return null
     */
    public function setDispatcher(Dispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Gets the dispatcher
     * @return zibo\library\mvc\dispatcher\Dispatcher
     */
    public function getDispatcher() {
        if (!$this->dispatcher) {
            $this->dispatcher = $this->getDependency('zibo\\library\\mvc\\dispatcher\\Dispatcher');
        }

        return $this->dispatcher;
    }

    /**
     * Services a HTTP request
     * @return null
     */
    public function service(HttpRequest $httpRequest = null) {
        $this->state = self::STATE_ROUTE;

        if ($this->log) {
            $start = $this->log->getTime();
        }

        if (!$httpRequest) {
            $httpRequest = $this->environment->getSapi()->getHttpRequest();
        }

        if ($this->log) {
            $method = $httpRequest->getMethod();

            $this->log->logDebug('Receiving request', $method . ' ' . $httpRequest->getPath(), self::LOG_SOURCE);

            $headers = $httpRequest->getHeaders();
            foreach ($headers as $header) {
                $this->log->logDebug('Receiving header', $header, self::LOG_SOURCE);
            }

            if ($httpRequest->getBody()) {
                $this->log->logDebug('Request body', $httpRequest->getBody(), self::LOG_SOURCE);
            }
        }

        $this->request = $httpRequest;
        $this->response = new Response();

        try {
            $this->route($httpRequest);

            $this->state = self::STATE_DISPATCH;

            // keep the initial request for the response
            $request = $this->getRequest();

            if (!$request && $this->response->getStatusCode() == Response::STATUS_CODE_OK && !$this->response->getView() && !$this->response->getBody()) {
                // there is no request to start the dispatch, forward to the web controller
                $method = $httpRequest->getMethod();
                if ($method == Request::METHOD_GET || $method == Request::METHOD_HEAD) {
                    $controller = $this->getDependency('zibo\\library\\mvc\\controller\\Controller', 'web');
                    $callback = array($controller, 'indexAction');

                    $route = new Route('/', $callback);
                    $route->setIsDynamic(true);
                    $route->setArguments(explode('/', ltrim($httpRequest->getBasePath(), '/')));

                    $this->setRequest(new Request($httpRequest, $route, $this->dependencyInjector));
                } else {
                    $this->response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);
                }
            }

            $this->dispatch();

            if ($request) {
                $this->setRequest($request);
            }
        } catch (Exception $exception) {
            if (isset($request) && $request) {
                $this->setRequest($request);
            }

            if ($this->log) {
                $this->log->logException($exception, self::LOG_SOURCE);
            }

            if ($this->eventManager->hasEventListeners(self::EVENT_EXCEPTION)) {
                $this->eventManager->triggerEvent(self::EVENT_EXCEPTION, $this, $exception);
            } else {
                throw $exception;
            }
        }

        if (!$this->request) {
            $this->request = $httpRequest;
        }

        $this->state = self::STATE_RESPONSE;

        $this->sendResponse();

        $this->request = null;
        $this->response = null;

        if ($this->log) {
            $stop = $this->log->getTime();
            $spent = $stop - $start;

            list($seconds, $nanoSeconds) = explode('.', $spent);
            $spent = $seconds . '.' .substr($nanoSeconds, 0, 4);

            $this->log->logDebug('Service took ' . $spent . ' seconds', null, self::LOG_SOURCE);
        }

        $this->state = self::STATE_IDLE;
    }

    /**
     * Gets a request object from the router and sets it to this instance of Zibo
     * @return null
     */
    protected function route(HttpRequest $request) {
        $method = $request->getMethod();
        $baseUrl = $request->getBaseUrl();
        $path = str_replace($request->getBaseScript(), '', $request->getUrl());

        if ($this->log) {
            $this->log->logDebug('Routing ' . $method . ' ' . $path, $baseUrl, self::LOG_SOURCE);
        }

        $this->eventManager->triggerEvent(self::EVENT_PRE_ROUTE, $this, $request);

        $router = $this->getRouter();

        $routerResult = $router->route($method, $path, $baseUrl);
        if (!$routerResult->isEmpty()) {
            $route = $routerResult->getRoute();
            if ($route) {
                $this->setRequest(new Request($request, $route, $this->dependencyInjector));
            } else {
                $this->setRequest(null);

                $allowedMethods = $routerResult->getAllowedMethods();

                $this->response->setStatusCode(Response::STATUS_CODE_METHOD_NOT_ALLOWED);
                $this->response->addHeader(Header::HEADER_ALLOW, implode(', ', $allowedMethods));

                if ($this->log) {
                    $this->log->logDebug('Requested method ' . $method . ' not allowed', null, self::LOG_SOURCE);
                }
            }
        } else {
            $this->setRequest(null);
        }

        $this->eventManager->triggerEvent(self::EVENT_POST_ROUTE, $this, $this->request);

        if (!$this->log) {
            return;
        }

        $request = $this->getRequest();
        if ($request) {
            $route = $request->getRoute();
            $this->log->logDebug('Routed to ' . $route, null, self::LOG_SOURCE);
        } else {
            $this->log->logDebug('No route matched', null, self::LOG_SOURCE);
        }
    }

    /**
     * Dispatch the request to the action of the controller
     * @return null
     */
    protected function dispatch() {
        if (!$this->request) {
            return;
        }

        $dispatcher = $this->getDispatcher();

        // request chaining
        while ($this->request) {
            $this->eventManager->triggerEvent(self::EVENT_PRE_DISPATCH, $this);

            if (!$this->request) {
                continue;
            }

            $chainedRequest = $dispatcher->dispatch($this->request, $this->response);

            if ($chainedRequest && !$chainedRequest instanceof Request) {
                throw new Exception('Action returned a invalid value, return nothing or a new zibo\\library\\mvc\\Request object for request chaining.');
            }

            $this->setRequest($chainedRequest);

            $this->eventManager->triggerEvent(self::EVENT_POST_DISPATCH, $this);
        }
    }

    /**
     * Sends the response to the client
     * @return null
     */
    protected function sendResponse() {
        $this->eventManager->triggerEvent(self::EVENT_PRE_RESPONSE, $this, $this->response);

        $this->setSessionCookie();

        $this->renderView();

        // send the response
        if ($this->log) {
            $this->log->logDebug('Sending response', 'Status code ' . $this->response->getStatusCode(), self::LOG_SOURCE);

            $headers = $this->response->getHeaders();
            foreach ($headers as $header) {
                $this->log->logDebug('Sending header', $header, self::LOG_SOURCE);
            }

            $cookies = $this->response->getCookies();
            foreach ($cookies as $cookie) {
                $this->log->logDebug('Sending header', Header::HEADER_SET_COOKIE . ': ' . $cookie, self::LOG_SOURCE);
            }

            $view = $this->response->getView();
            if ($view) {
                $this->log->logDebug('Rendering and sending view', get_class($view), self::LOG_SOURCE);
            }
        }

        $this->environment->getSapi()->sendHttpResponse($this->response);

        $this->eventManager->triggerEvent(self::EVENT_POST_RESPONSE, $this);

        // write the session
        if ($this->request->hasSession()) {
            $session = $this->request->getSession();
            $session->write();

            if ($this->log) {
                $this->log->logDebug('Current session:', $session->getId(), self::LOG_SOURCE);

                $variables = $session->getAll();
                ksort($variables);
                foreach ($variables as $name => $value) {
                    $this->log->logDebug('- ' . $name, var_export($value, true), self::LOG_SOURCE);
                }
            }
        } elseif ($this->log) {
            $this->log->logDebug('No session loaded', '', self::LOG_SOURCE);
        }
    }

    /**
     * Renders the view.
     *
     * <p>Render the view before sending the status code and headers. This way
     * the error handler can still create a clean response if an exception
     * occurs while rendering the view.</p>
     * @return null
     * @throws Exception When an exception occurs and no event listener is
     * registered to the exception event.
     */
    protected function renderView() {
        try {
            $view = $this->response->getView();
            if ($view) {
                if ($this->log) {
                    $this->log->logDebug('Rendering view', get_class($view), self::LOG_SOURCE);
                }

                if (!$view instanceof FileView) {
                    $body = $view->render(true);

                    $this->response->setBody($body);
                    $this->response->setView(null);
                }
            }
        } catch (Exception $exception) {
            if ($this->log) {
                $this->log->logException($exception, self::LOG_SOURCE);
            }

            if ($this->eventManager->hasEventListeners(self::EVENT_EXCEPTION)) {
                $this->eventManager->triggerEvent(self::EVENT_EXCEPTION, $this, $exception);
            } else {
                throw $exception;
            }
        }
    }

    /**
     * Sets the session cookie if not set
     * @return null
     */
    protected function setSessionCookie() {
        if (!$this->request->hasSession()) {
            return;
        }

        $cookieName = $this->request->getSessionCookieName();
        $session = $this->request->getSession();

        $timeout = $this->getParameter(self::PARAM_SESSION_TIMEOUT, self::DEFAULT_SESSION_TIMEOUT);
        if ($timeout) {
            $expires = time() + $timeout;
        } else {
            $expires = 0;
        }

        $domain = $this->request->getHeader(Header::HEADER_HOST);
        $path = str_replace($this->request->getServerUrl(), '', $this->request->getBaseUrl());
        if (!$path) {
            $path = '/';
        }

        $cookie = new Cookie($cookieName, $session->getId(), $expires, $domain, $path);
        $this->response->setCookie($cookie);
    }

}
