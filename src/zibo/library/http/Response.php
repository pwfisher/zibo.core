<?php

namespace zibo\library\http;

use \Exception;

/**
 * Data container for a HTTP response
 */
class Response {

    /**
     * HTTP status code for a ok status
     * @var int
     */
    const STATUS_CODE_OK = 200;

    /**
     * HTTP status code for a created status
     * @var int
     */
    const STATUS_CODE_CREATED = 201;

    /**
     * HTTP status code for a no content status
     * @var int
     */
    const STATUS_CODE_NO_CONTENT = 204;

    /**
     * HTTP status code for a moved permanently status
     * @var int
     */
    const STATUS_CODE_MOVED_PERMANENTLY = 301;

    /**
     * HTTP status code for a found status
     * @var int
     */
    const STATUS_CODE_FOUND = 302;

    /**
     * HTTP status code for a not modified status
     * @var int
     */
    const STATUS_CODE_NOT_MODIFIED = 304;

    /**
     * HTTP status code for a bad request status
     * @var int
     */
    const STATUS_CODE_BAD_REQUEST = 400;

    /**
     * HTTP status code for a unauthorized status
     * @var int
     */
    const STATUS_CODE_UNAUTHORIZED = 401;

    /**
     * HTTP status code for a forbidden status
     * @var int
     */
    const STATUS_CODE_FORBIDDEN = 403;

    /**
     * HTTP status code for a not found status
     * @var int
     */
    const STATUS_CODE_NOT_FOUND = 404;

    /**
     * HTTP status code for a method not allowed status
     * @var int
     */
    const STATUS_CODE_METHOD_NOT_ALLOWED = 405;

    /**
     * HTTP status code for a server error status
     * @var int
     */
    const STATUS_CODE_SERVER_ERROR = 500;

    /**
     * HTTP status code for a unimplemented request
     * @var int
     */
    const STATUS_CODE_NOT_IMPLEMENTED = 501;

    /**
     * HTTP status code
     * @var unknown_type
     */
    const STATUS_CODE_SERVICE_UNAVAILABLE = 503;

    /**
     * The HTTP response status code
     * @var int
     */
    protected $statusCode;

    /**
     * Container of the headers assigned to this response
     * @var zibo\library\http\HeaderContainer
     */
    protected $headers;

    /**
     * Array with Cookie objects
     * @var array
     */
    protected $cookies;

    /**
     * The timestamp of the date of the response
     * @var integer
     */
    protected $date;

    /**
     * The timestamp of the last modified date of the content
     * @var integer
     */
    protected $dateLastModified;

   /**
     * The body of this response
     * @var string
     */
    protected $body;

    /**
     * Construct a new response
     * @return null
     */
    public function __construct() {
        $this->statusCode = self::STATUS_CODE_OK;

        $this->date = time();
        $this->dateLastModified = null;

        $this->headers = new HeaderContainer();
        $this->headers->setHeader(Header::HEADER_DATE, Header::parseTime($this->date));

        $this->cookies = array();

        $this->body = null;
    }

    /**
     * Gets a string representation of this response
     * @return string
     */
    public function __toString() {
        $request = $this->statusCode . "\r\n";

        foreach ($this->headers as $header) {
            $request .= (string) $header . "\r\n";
        }

        foreach ($this->cookies as $cookie) {
            $request .= Header::HEADER_SET_COOKIE . ': ' . $cookie . "\r\n";
        }

        if ($this->body) {
            $request .= "\r\n" . $this->body . "\r\n";
        }

        return $request;
    }

    /**
     * Sets the HTTP status code. At Wikipedia you can find a
     * {@link http://en.wikipedia.org/wiki/List_of_HTTP_status_codes list of HTTP status codes}.
     * @param integer $code The HTTP status code
     * @return null
     * @see STATUS_CODE_OK, STATUS_CODE_MOVED_PERMANENTLY, STATUS_CODE_FOUND,
     * STATUS_CODE_NOT_MODIFIED, STATUS_CODE_NOT_FOUND
     * @throws Exception when the provided response code is not a
     * valid reponse code
     */
    public function setStatusCode($code) {
        if (!is_integer($code) || $code < 100 || $code > 599) {
            throw new Exception('Provided code is an invalid status code');
        }

        $this->statusCode = $code;
    }

	/**
	 * Returns the current HTTP status code.
     * @return integer
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Adds a HTTP header.
     *
     * On Wikipedia you can find a {@link http://en.wikipedia.org/wiki/List_of_HTTP_headers list of HTTP headers}.
     * If a Locaton header is added, the status code will also be automatically
     * set to 302 Found if the current status code is 200 OK.
     * @param string $name the name of the header
     * @param string $value the value of the header
     * @return null
     * @throws Exception when the provided name is empty or invalid
     * @throws Exception when the provided value is empty or invalid
     * @see setHeader()
     */
    public function addHeader($name, $value) {
        $header = new Header($name, $value);

        $name = $header->getName();

        if ($name == Header::HEADER_LOCATION && !$this->willRedirect()) {
            $this->setStatusCode(self::STATUS_CODE_FOUND);
        }

        if ($name == Header::HEADER_DATE) {
            $this->date = Header::parseTime($value);
            $this->headers->setHeader($header);
        } else {
            $this->headers->addHeader($header);
        }
    }

    /**
     * Sets a HTTP header, replacing any previously added HTTP headers with
     * the same name.
     * @param string $name the name of the header
     * @param string $value the value of the header
     * @return null
     * @throws Exception when the provided name is empty or invalid
     * @throws Exception when the provided value is empty or invalid
     * @see addHeader()
     */
    public function setHeader($name, $value) {
        $this->headers->removeHeader($name);
        $this->addHeader($name, $value);
    }

    /**
     * Checks if a header is set
     * @param string $name The name of the header
     * @return boolean True if the header is set, false otherwise
     */
    public function hasHeader($name) {
        return $this->headers->hasHeader($name);
    }

    /**
    * Gets a HTTP header value
    * @param string $name Name of the header
    * @return string|array|null The value of the header, an array of values if
    * the header is set multiple times, null if not set
    * @see zibo\library\http\Header
    */
    public function getHeader($name) {
        $header = $this->headers->getHeader($name);
        if (!$header) {
            return null;
        }

        if (!is_array($header)) {
            return $header->getValue();
        }

        $values = array();
        foreach ($header as $h) {
            $values[] = $h->getValue();
        }

        return $values;
    }

    /**
     * Returns the HTTP headers.
     * @return zibo\library\http\HeaderContainer The container of the HTTP headers
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Removes a HTTP header.
     * @param string $name the name of the header you want to remove
     * @return null
     * @throws Exception when the provided name is empty or invalid
     */
    public function removeHeader($name) {
        $this->headers->removeHeader($name);
    }

    /**
     * Sets a cookie
     * @param Cookie $cookie
     * @return null
     */
    public function setCookie(Cookie $cookie) {
        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * Gets a cookie by name
     * @param string $name Name of the cookie
     * @return Cookie|null Instance of the cookie or null if the cookie was not
     * set
     */
    public function getCookie($name) {
        if (!isset($this->cookies[$name])) {
            return null;
        }

        return $this->cookies[$name];
    }

    /**
     * Gets all the cookies
     * @return array Array with the name of the cookie as key and a instance of
     * Cookie as value
     * @see Cookie
     */
    public function getCookies() {
        return $this->cookies;
    }

    /**
     * Sets the HTTP headers required to make the browser redirect.
     * @param string $url The URL to redirect to
     * @param string $statusCode The status code for this redirect
     * @return null
     * @throws Exception when the provided status code is not a
     * valid redirect status code
     */
    public function setRedirect($url, $statusCode = null) {
        if ($statusCode) {
            if ($statusCode < 300 || 400 <= $statusCode) {
                throw new Exception('Invalid redirect status code provided');
            }

            $this->setStatusCode($statusCode);
        }

        $this->setHeader(Header::HEADER_LOCATION, $url);
    }

    /**
     * Checks if the response will redirect
     * @return boolean True if the status code is a redirect code, false otherwise
     */
    public function willRedirect() {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Removes the HTTP headers that cause the browser to redirect.
     *
     * The HTTP status code of the response will be reset to 200 OK.
     * @return null
     */
    public function clearRedirect() {
        if (!$this->willRedirect()) {
            return;
        }

        $this->headers->removeHeader(Header::HEADER_LOCATION);

        $this->statusCode = self::STATUS_CODE_OK;
    }

    /**
     * Sets the date the content will become stale
     * @param integer $timestamp Timestamp of the date
     * @return null
     */
    public function setExpires($timestamp = null) {
        if ($timestamp === null) {
            $this->headers->removeHeader(Header::HEADER_EXPIRES);
            return;
        }

        if (!is_long($timestamp)) {
            throw new Exception('Invalid timestamp provided');
        }

        $this->headers->setHeader(Header::HEADER_EXPIRES, Header::parseTime($timestamp));
    }

    /**
     * Gets the date the content was become stale
     * @return integer|null Timestamp of the date if set, null otherwise
     */
    public function getExpires() {
        $header = $this->headers->getHeader(Header::HEADER_EXPIRES);
        if (!$header) {
            return null;
        }

        return Header::parseTime($header->getValue());
    }

    /**
     * Sets or unsets the public cache control directive.
     *
     * When set to true, all caches may cache the response.
     * @param boolean $flag Set to false to unset the directive, true sets it
     * @return null
     */
    public function setIsPublic($flag = true) {
        $this->headers->removeCacheControlDirective(HeaderContainer::CACHE_CONTROL_PRIVATE);
        if ($flag) {
            $this->headers->addCacheControlDirective(HeaderContainer::CACHE_CONTROL_PUBLIC);
        } else {
            $this->headers->removeCacheControlDirective(HeaderContainer::CACHE_CONTROL_PUBLIC);
        }
    }

    /**
     * Gets the public cache control directive
     * @return boolean|null True if set, null otherwise
     */
    public function isPublic() {
        return $this->headers->getCacheControlDirective(HeaderContainer::CACHE_CONTROL_PUBLIC);
    }

    /**
     * Sets or unsets the private cache control directive
     *
     * When set to true, a shared cache must not cache the response.
     * @param boolean $flag Set to false to unset the directive, true or any value sets it
     * @return null
     */
    public function setIsPrivate($flag = true) {
        $this->headers->removeCacheControlDirective(HeaderContainer::CACHE_CONTROL_PUBLIC);
        if ($value !== false) {
            $this->headers->addCacheControlDirective(HeaderContainer::CACHE_CONTROL_PRIVATE, $value);
        } else {
            $this->headers->removeCacheControlDirective(HeaderContainer::CACHE_CONTROL_PRIVATE);
        }
    }

    /**
     * Gets the private cache control directive
     * @return boolean|string|null True or the field if set, null otherwise
     */
    public function isPrivate() {
        return $this->headers->getCacheControlDirective(HeaderContainer::CACHE_CONTROL_PRIVATE);
    }

    /**
     * Sets the max age cache control directive
     *
     * When set to true, a shared cache must not cache the response.
     * @param boolean $flag Set to false to unset the directive, true or any value sets it
     * @return null
     */
    public function setMaxAge($seconds = null) {
        if ($seconds === null) {
            $this->headers->removeCacheControlDirective(HeaderContainer::CACHE_CONTROL_MAX_AGE);
            return;
        }

        if (!is_long($seconds) || $seconds <= 0) {
            throw new Exception('The max age should be a unsigned long');
        }

        $this->headers->addCacheControlDirective(HeaderContainer::CACHE_CONTROL_MAX_AGE, $seconds);
    }

    /**
     * Gets the max age cache control directive
     * @return integer|null Seconds if set, null otherwise
     */
    public function getMaxAge() {
        return $this->headers->getCacheControlDirective(HeaderContainer::CACHE_CONTROL_MAX_AGE);
    }

    /**
     * Sets the shared max age cache control directive
     *
     * This will make your response public
     * @param boolean $flag Set to false to unset the directive, true or any value sets it
     * @return null
     * @see setIsPublic()
     */
    public function setSharedMaxAge($seconds = null) {
        if ($seconds === null) {
            $this->headers->removeCacheControlDirective(HeaderContainer::CACHE_CONTROL_SHARED_MAX_AGE);
            return;
        }

        if (!is_long($seconds) || $seconds <= 0) {
            throw new Exception('The max age should be a unsigned long');
        }

        $this->headers->addCacheControlDirective(HeaderContainer::CACHE_CONTROL_SHARED_MAX_AGE, $seconds);
        $this->setIsPublic();
    }

    /**
     * Gets the shared max age cache control directive
     * @return integer|null Seconds if set, null otherwise
     */
    public function getSharedMaxAge() {
        return $this->headers->getCacheControlDirective(HeaderContainer::CACHE_CONTROL_SHARED_MAX_AGE);
    }

    /**
     * Sets the date the content was last modified
     * @param integer $timestamp Timestamp of the date
     * @return null
     */
    public function setLastModified($timestamp = null) {
        if ($timestamp === null) {
            $this->dateLastModified = null;
            $this->headers->removeHeader(Header::HEADER_LAST_MODIFIED);
            return;
        }

        if (!is_numeric($timestamp)) {
            throw new Exception('Invalid timestamp provided');
        }

        $this->dateLastModified = $timestamp;

        $this->headers->setHeader(Header::HEADER_LAST_MODIFIED, Header::parseTime($timestamp));
    }

    /**
     * Gets the date the content was last modified
     * @return integer|null Timestamp of the date if set, null otherwise
     */
    public function getLastModified() {
        return $this->dateLastModified;
    }

    /**
     * Sets the ETag
     * @param string $eTag A unique identifier of the current version of
     * the content
     * @return null
     */
    public function setETag($eTag = null) {
        if ($eTag === null) {
            $this->headers->removeHeader(Header::HEADER_ETAG);
        } else {
            $this->headers->setHeader(Header::HEADER_ETAG, $eTag);
        }
    }

    /**
     * Gets the ETag
     * @return null|string A unique identifier of the current version of
     * the content if set, null otherwise
     */
    public function getETag() {
        $header = $this->headers->getHeader(Header::HEADER_ETAG);

        if (!$header) {
            return null;
        }

        return $header->getValue();
    }

    /**
     * Checks if the current status is not modified. If the status code is set
     * @param zibo\core\Request $request
     * @return boolean True if the content is not modified, false otherwise
     */
    public function isNotModified(Request $request) {
        $noneMatch = $request->getIfNoneMatch();
        $modifiedSince = $request->getIfModifiedSince();

        $eTag = $this->getETag();

        $isNoneMatch = !$noneMatch || isset($noneMatch['*']) || ($eTag && isset($noneMatch[$eTag]));
        $isModifiedSince = !$modifiedSince || $this->getLastModified() == $modifiedSince;

        $isNotModified = false;
        if ($noneMatch && $modifiedSince) {
            $isNotModified = $isNoneMatch && $isModifiedSince;
        } elseif ($noneMatch) {
            $isNotModified = $isNoneMatch;
        } elseif ($modifiedSince) {
            $isNotModified = $isModifiedSince;
        }

        return $isNotModified;
    }

    /**
     * Sets the response status code to not modified and removes illegal headers
     * for such a response code
     * @return null
     */
    public function setNotModified() {
        $this->setStatusCode(self::STATUS_CODE_NOT_MODIFIED);
        $this->setView(null);

        $removeHeaders = array(
            Header::HEADER_ALLOW,
            Header::HEADER_CONTENT_ENCODING,
            Header::HEADER_CONTENT_LANGUAGE,
            Header::HEADER_CONTENT_LENGTH,
            Header::HEADER_CONTENT_MD5,
            Header::HEADER_CONTENT_TYPE,
            Header::HEADER_LAST_MODIFIED,
        );

        $this->headers->removeHeader($removeHeaders);
    }

    /**
     * Sets the body of this response.
     * @param string $body The body
     * @return null
     */
    public function setBody($body = null) {
        $this->body = $body;
    }

    /**
     * Returns the body of this response
     * @return string The body
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Sends the response to the client
	 * @param Request $request The request to respond to
     * @return null
     */
    public function send(Request $request) {
    	$this->sendHeaders($request->getProtocol());

    	if ($this->willRedirect()) {
    	    return;
    	}

		echo $this->body;
    }

    /**
     * Sets the status code and sends the headers to the client
     * @param int $statusCode HTTP response status code
     * @param zibo\library\http\HeaderContainer $headers Container of the headers
     * @return null
     * @throws Exception when the output already started
     * @see zibo\library\http\Header
     */
    protected function sendHeaders($protocol) {
		if (!$this->headers->hasHeaders() && $this->statusCode === Response::STATUS_CODE_OK) {
			return;
		}

		if (headers_sent($file, $line)) {
			throw new Exception('Cannot send headers, output already started in ' . $file . ' on line ' . $line);
		}

		// set the status code
		header($protocol . ' ' . $this->statusCode);

    	// set the headers
        foreach ($this->headers as $header) {
        	header((string) $header, false);
		}

		// set the cookies
		foreach ($this->cookies as $cookie) {
		    header(Header::HEADER_SET_COOKIE . ': ' . $cookie, false);
		}
    }

    /**
     * Creates a object from a raw HTTP response
     * @param string $data Raw HTTP response
     * @param string $lineBreak Line break of the response
     * @return zibo\library\http\Response
     * @throws zibo\library\http\exception\HttpException when the raw HTTP
     * response is not valid
     */
    public static function createFromString($data, $lineBreak = "\r\n") {
        $response = new self();

        $lines = explode($lineBreak, $data);

        // get the status code
        $status = array_shift($lines);

        preg_match('#^HTTP/.* ([0-9]{3,3})( (.*))?#i', $status, $matches);
        if (isset($matches[1])) {
            $response->setStatusCode((integer) $matches[1]);
        } else {
            throw new HttpException('Could not parse the response: no HTTP response');
        }

        // get the headers
        $emptyLine = false;
        while (!$emptyLine) {
            $line = array_shift($lines);
            $line = trim($line);

            if (!$line) {
                $emptyLine = true;

                continue;
            }

            $position = strpos($line, ': ');
            if (!$position) {
                continue;
            }

            list($name, $value) = explode(': ', $line, 2);

            $response->addHeader($name, $value);
        }

        // get the content
        $body = '';
        while ($lines) {
            $line = array_shift($lines);
            $body .= $line . $lineBreak;
        }

        $response->setBody($body);

        return $response;
    }

}