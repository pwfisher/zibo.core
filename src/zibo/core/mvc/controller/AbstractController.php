<?php

namespace zibo\core\mvc\controller;

use zibo\core\mvc\view\FileView;
use zibo\core\Mime;
use zibo\core\Zibo;

use zibo\library\filesystem\File;
use zibo\library\http\Header;
use zibo\library\mvc\controller\AbstractController as MvcAbstractController;
use zibo\library\mvc\exception\MvcException;
use zibo\library\mvc\Request;

/**
 * Abstract implementation of a controller
 */
abstract class AbstractController extends MvcAbstractController implements ZiboController {

    /**
     * Instance of Zibo
     * @var zibo\core\Zibo
     */
    protected $zibo;

    /**
     * Sets the instance of Zibo to this controller
     * @param zibo\core\Zibo $zibo
     * @return null
     */
    public function setZibo(Zibo $zibo) {
        $this->zibo = $zibo;
    }

    /**
     * Gets the instance of Zibo from this controller
     * @return zibo\core\Zibo $zibo The instance of Zibo
     */
    public function getZibo() {
        return $this->zibo;
    }

    /**
     * Gets the URL of the provided route
     * @param string $routeId The id of the route
     * @param array $arguments Path arguments for the route
     * @return string
     * @throws zibo\library\router\exception\RouterException If the route is
     * not found
     */
    protected function getUrl($routeId, array $arguments = null) {
        return $this->zibo->getUrl($routeId, $arguments);
    }

    /**
     * Parses an array of values into a key value array. Usefull to parse the
     * arguments of an action
     *
     * eg. array('key1', 'value1', 'key2', 'value2')
     * will return
     * array('key1' => 'value1', 'key2' => 'value2')
     * @param array $arguments Arguments array
     * @return array Parsed arguments array
     * @throws Exception when the number of elements in the argument array is
     * not even
     */
    protected function parseArguments(array $arguments) {
        if (count($arguments) % 2 != 0) {
            throw new Exception('Provided arguments array should have an even number of arguments');
        }

        $parsedArguments = array();

        $argumentName = null;
        foreach ($arguments as $argument) {
            if ($argumentName === null) {
                $argumentName = $argument;
            } else {
                $parsedArguments[$argumentName] = urldecode($argument);
                $argumentName = null;
            }
        }

        return $parsedArguments;
    }

    /**
     * Sets a download view for the provided file to the response
     * @param zibo\library\filesystem\File $file File which needs to be offered
     * for download
     * @param string $name Name for the download
     * @param boolean $cleanUp Set to true to register an event to clean up the
     * file after the response has been sent
     * @return null
     */
    protected function setDownloadView(File $file, $name = null, $cleanUp = false) {
        if ($name === null) {
            $name = $file->getName();
        }

        $userAgent = $this->request->getHeader(Header::HEADER_USER_AGENT);
        if ($userAgent && strstr($userAgent, "MSIE")) {
            $name = preg_replace('/\./', '%2e', $name, substr_count($name, '.') - 1);
        }

        $mime = Mime::getMimeType($this->zibo, $file);

        $view = new FileView($file);

        $this->response->setHeader(Header::HEADER_CACHE_CONTROL, 'no-cache, must-revalidate');
        $this->response->setHeader(Header::HEADER_CONTENT_TYPE, $mime);
        $this->response->setHeader(Header::HEADER_CONTENT_DESCRIPTION, 'File Transfer');
        $this->response->setHeader(Header::HEADER_CONTENT_DISPOSITION, 'attachment; filename="' . $name . '"');
        $this->response->setView($view);

        if ($cleanUp) {
            $this->downloadFile = $file;
            $this->zibo->registerEventListener(Zibo::EVENT_POST_RESPONSE, array($this, 'cleanUpDownload'));
        }
    }

    /**
     * Cleans up the download file
     * @return null
     */
    public function cleanUpDownload() {
        if (isset($this->downloadFile) && $this->downloadFile) {
            $this->downloadFile->delete();
        }
    }

}