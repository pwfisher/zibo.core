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
     * Constructs a new HTTP client
     * @return null
     * @throws zibo\library\http\exception\HttpException when cURL is not
     * available
     */
    public function __construct() {
        if (!function_exists('curl_init')) {
            throw new HttpException('Could not construct the client: cURL extension for PHP is not installed');
        }
    }

    /**
     * Performs a request
     * @param zibo\library\http\Request $request The request to send
     * @return zibo\library\http\Response The reponse of the request
     * @see zibo\library\network\Connection
     */
    public function sendRequest(Request $request) {
        $options = array(
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $request->getUrl(),
        );

        $headers = (string) $request->getHeaders();
        $headers = trim($headers);
        if ($headers) {
            $options[CURLOPT_HTTPHEADER] = explode("\r\n", $headers);
        }

        if ($request->getBodyParameters()) {
            $options[CURLOPT_POSTFIELDS] = $request->getBodyParameters();
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        if ($this->log) {
            $this->log->logDebug('Sending request', $request, self::LOG_SOURCE);
        }

        $responseString = curl_exec($curl);

        if ($this->log) {
            $this->log->logDebug('Received response', $responseString, self::LOG_SOURCE);
        }

        curl_close($curl);

        return Response::createFromString($responseString);
    }

}