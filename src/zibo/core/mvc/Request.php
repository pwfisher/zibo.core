<?php

namespace zibo\core\mvc;

use zibo\library\dependency\DependencyInjector;
use zibo\library\http\Request as HttpRequest;
use zibo\library\mvc\exception\MvcException;
use zibo\library\mvc\Request as MvcRequest;
use zibo\library\router\Route;
use zibo\library\String;

/**
 * A extension of the MVC request for automatic session handling
 */
class Request extends MvcRequest {

    /**
     * Default name for the cookie of the session id
     * @var string
     */
    const DEFAULT_SESSION_COOKIE = 'sid';

    /**
     * Instance of the dependency injector
     * @var zibo\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Name of the cookie for the session id
     * @var strubg
     */
    protected $sessionCookieName;

    /**
     * Constructs a new request
     * @param zibo\library\http\Request $request A HTTP request
     * @param zibo\library\router\Route $route The selected route
     * @param zibo\library\dependency\DependencyInjector $dependencyInjector
     * Instance of the dependency injector to load the session implementation
     * @param string $sessionCookieName Name of the cookie for the session id
     * @return null
     */
    public function __construct(HttpRequest $request, Route $route, DependencyInjector $dependencyInjector = null, $sessionCookieName = null) {
        parent::__construct($request, $route);

        if (!$sessionCookieName) {
            $sessionCookieName = self::DEFAULT_SESSION_COOKIE;
        }

        if ($request instanceof self) {
            $this->dependencyInjector = $request->dependencyInjector;
            $this->sessionCookieName = $sessionCookieName;

            return;
        }

        if (!$dependencyInjector) {
            throw new MvcException('No dependency injector provided');
        }

        $this->dependencyInjector = $dependencyInjector;
        $this->sessionCookieName = $sessionCookieName;
    }

    /**
     * Gets the properties to be serialized
     * @return array Array with property names
     */
    public function __sleep() {
        $properties = parent::__sleep();
        $properties[] = 'sessionCookieName';

        return $properties;
    }

    /**
     * Checks if a session has been set
     * @return boolean
     */
    public function hasSession() {
        return !empty($this->session) || $this->getCookie($this->sessionCookieName);
    }

    /**
     * Gets the session container
     * @return zibo\library\http\session\Session
     */
    public function getSession() {
        if ($this->session) {
            return $this->session;
        }

        $sessionId = $this->getCookie($this->sessionCookieName);
        if (!$sessionId) {
            $sessionId = $this->getQueryParameter($this->sessionCookieName);
        }

        if (!$sessionId) {
            $sessionId = md5(time() . String::generate());
        }

        $this->session = $this->dependencyInjector->get('zibo\library\http\session\Session');
        $this->session->read($sessionId);

        return $this->session;
    }

    /**
     * Gets the name for the session cookie
     * @return string
     */
    public function getSessionCookieName() {
        return $this->sessionCookieName;
    }

}