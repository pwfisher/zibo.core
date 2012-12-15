<?php

namespace zibo\core\mvc\controller;

use zibo\core\mvc\view\FileView;
use zibo\core\Mime;
use zibo\core\Zibo;

use zibo\library\filesystem\File;
use zibo\library\http\Header;
use zibo\library\http\Request;
use zibo\library\http\Response;

/**
 * Controller to host files from the web directories in the Zibo include paths
 */
class WebController extends AbstractController {

    /**
     * Action to host a file. The filename is provided by the arguments as tokens
     * @return null
     */
    public function indexAction() {
        // get the requested path of the file
        $args = func_get_args();
        $path = implode('/', $args);

        if (empty($path)) {
            // no path provided
            $this->response->setStatusCode(Response::STATUS_CODE_BAD_REQUEST);
            return;
        }

        // lookup the file
        $file = $this->getFile($path);

        if (!$file) {
            // file not found, set status code
            $this->response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);
            return;
        }

        if ($file->getExtension() == 'php') {
            // the file is a PHP script, execute it
            require_once($file->getAbsolutePath());
            return;
        }

        // get needed file properties
        $fileModificationTime = $file->getModificationTime();
        $fileSize = $file->getSize();

        // set cache headers
        $eTag = md5($path . '-' . $fileModificationTime . '-' . $fileSize);
        $maxAge = 3600; // an hour
        $expirationTime = time() + $maxAge;

        $this->response->setETag($eTag);
        $this->response->setLastModified($fileModificationTime);
        $this->response->setExpires($expirationTime);
        $this->response->setMaxAge($maxAge);
        $this->response->setSharedMaxAge($maxAge);

        if ($this->response->isNotModified($this->request)) {
            // content is not modified, stop processing
            $this->response->setNotModified();
            return;
        }

        // set content headers
        $mime = Mime::getMimeType($this->zibo, $file);

        $this->response->setHeader(Header::HEADER_CONTENT_TYPE, $mime);
        $this->response->setHeader(Header::HEADER_CONTENT_LENGTH, $fileSize);

        if ($this->request->getMethod() != Request::METHOD_HEAD) {
            // don't send content when this is a HEAD request
            $this->response->setView(new FileView($file));
        }
    }

    /**
     * Gets the file from the Zibo include path
     * @param string $path Relative path of the file in the web directory
     * @return null|zibo\library\filesystem\File
     */
    protected function getFile($path) {
        $plainPath = new File(Zibo::DIRECTORY_WEB . File::DIRECTORY_SEPARATOR . $path);

        $file = $this->zibo->getFile($plainPath);
        if ($file) {
            return $file;
        }

        $encodedPath = new File($plainPath->getParent()->getPath(), urlencode($plainPath->getName()));

        return $this->zibo->getFile($encodedPath);
    }

}