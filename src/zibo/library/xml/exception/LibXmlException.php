<?php

namespace zibo\library\xml\exception;

use zibo\library\String;

use \Exception;

/**
 * Exception for the errors of PHP's internal XML library
 */
class LibXmlException extends Exception {

    /**
     * Construct this exception
     * @param string $message
     * @param string $code
     * @param array $errors an array with LibXMLError objects
     * @param string $source source of the document or file name to the source
     * @return null
     */
    public function __construct($message = null, $code = null, array $errors = array(), $source = null) {
        if (!$message) {
            $message = "XML error";
        }

        if (count($errors) > 0) {
            $message .= "\nErrors reported by libxml:\n";
            foreach ($errors as $error) {
                $message .= '- ' . $error->file . ':' . $error->line . ' column ' . $error->column . ': ' . $error->message;
            }
        }

//         if ($source) {
//             $message .= '<br /><br /><pre>Source: ' . PHP_EOL . String::addLineNumbers($source) . '</pre><br /><br />';
//         }

        parent::__construct($message, $code);
    }

    /**
     * Get the name of the error level
     * @param int $level level of a LibXMLError
     * @return string name of the error level
     */
    private function getErrorLevelName($level) {
        switch ($level) {
            case LIBXML_ERR_WARNING:
                return 'warning';
            case LIBXML_ERR_ERROR:
                return 'error';
            case LIBXML_ERR_FATAL:
                return 'fatal error';
        }
    }

}