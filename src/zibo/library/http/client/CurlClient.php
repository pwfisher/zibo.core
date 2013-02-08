<?php

namespace zibo\library\http\client;

use zibo\library\http\exception\HttpException;
use zibo\library\http\Request;
use zibo\library\http\Response;

/**
 * cURL implementation of the HTTP client
 */
class CurlClient extends AbstractClient {

    /**
     * Flag to see if the location header in the response should be followed
     * @var boolean
     */
    protected $followLocation;

    /**
     * Constructs a new HTTP client
     * @return null
     * @throws zibo\library\http\exception\HttpException when cURL is not
     * available
     */
    public function __construct() {
        if (!function_exists('curl_init')) {
            throw new HttpException('Could not construct the client: cURL extension for PHP is not installed');
        }

        $this->followLocation = false;
    }

    /**
     * Sets whether the location header in the response should be followed
     * @param boolean $followLocation
     * @return null
     */
    public function setFollowLocation($followLocation) {
        $this->followLocation = $followLocation;
    }

    /**
     * Gets whether the location header in the response should be followed
     * @return boolean
     */
    public function willFollowLocation() {
        return $this->followLocation;
    }

    /**
     * Performs a HTTP request
     * @param zibo\library\http\Request $request The request to send
     * @return zibo\library\http\Response The reponse of the request
     */
    public function sendRequest(Request $request) {
        $options = array(
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL => $request->getUrl(),
            CURLOPT_FOLLOWLOCATION => $this->followLocation,
            CURLOPT_HEADER => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_VERBOSE => true,
//            CURLINFO_HEADER_OUT => true,
        );

        $headers = (string) $request->getHeaders();
        $headers = trim($headers);
        if ($headers) {
            $options[CURLOPT_HTTPHEADER] = explode("\r\n", $headers);

            if (!$request->getHeaders()->hasHeader('Expect')) {
                $options[CURLOPT_HTTPHEADER][] = 'Expect:';
            }
        }

        $body = $request->getBody();
        if ($body) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        if ($this->username) {
            $method = $this->getAuthenticationMethod();

            switch (strtolower($method)) {
                case self::AUTHENTICATION_METHOD_BASIC:
                    $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;

                    break;
                case self::AUTHENTICATION_METHOD_DIGEST:
                    $options[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;

                    break;
                default:
                    throw new HttpException('Could not send the request: invalid authentication method set (' . $method . ')');

                    break;
            }

            $options[CURLOPT_USERPWD] = $this->username . ':' . $this->password;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        if ($this->log) {
            $this->log->logDebug('Sending ' . ($request->isSecure() ? 'secure ' : '') . 'request', $request, self::LOG_SOURCE);

            if ($this->username) {
                $this->log->logDebug('Authorization', $method . ' ' . $this->username, self::LOG_SOURCE);
            }
        }

        $responseString = curl_exec($curl);

        $error = curl_error($curl);
        if ($error) {
            throw new HttpException('cURL returned error: ' . $error);
        }

        if ($this->log) {
//            $this->log->logDebug(var_export(curl_getinfo($curl), true), null, self::LOG_SOURCE);
            $this->log->logDebug('Received response', $responseString, self::LOG_SOURCE);
        }

        curl_close($curl);

        return Response::createFromString($responseString);
    }

}