<?php

namespace zibo\library\mvc\view;

use zibo\library\String;

use \Exception;

/**
 * View to display an exception
 */
class ExceptionView implements View {

    /**
     * The exception to display
     * @var string
     */
    protected $exception;

    /**
     * Flag to see if HTML should be outputted
     * @var boolean
     */
    protected $isHtml;

    /**
     * Constructs a new exception view
     * @param Exception $exception
     * @return null
     */
    public function __construct(Exception $exception) {
        $this->exception = $exception;
    }

    /**
     * Sets whether this view outputs HTML or plain text
     * @param boolean $isHtml True for HTML
     * @return null
     */
    public function setIsHtml($isHtml) {
        $this->isHtml = $isHtml;
    }

    /**
     * Renders the output for this view
     * @param boolean $willReturnValue True to return the rendered view, false
     * to send it straight to the client
     * @return mixed Null when provided $willReturnValue is set to true, the
     * rendered output otherwise
     */
    public function render($willReturnValue = true) {
        $exception = self::getExceptionArray($this->exception);
        $source = self::getExceptionSource($this->exception);

        if ($this->isHtml) {
            $output = $this->renderHtml($exception, $source);
        } else {
            $output = $this->renderPlain($exception, $source);
        }


        if ($willReturnValue) {
            return $output;
        }

        echo $output;
    }

    protected function renderHtml(array $exception, $source) {
        $output = "<!DOCTYPE html>\n";
        $output .= "<html>\n";
        $output .= "    <head>\n";
        $output .= "        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
        $output .= "        <title>:'-( Whoopsie!</title>\n";
        $output .= "        <style>pre { padding: 5px; background-color: #272727; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; } a { color: #fc0; }</style>\n";
        $output .= "    </head>\n";
        $output .= "    <body style=\"background-color: #1D1D1D; color: #FFF; font-family: sans-serif;\">\n";
        $output .= "        <div style=\"padding-left: 75px;\">\n";
        $output .= "            <div style=\"position: absolute; left: 20px; top: 25px; width: 50px; font-weight: bold; font-size: 1.5em;\">:'-(</div>\n";
        $output .= "			<h1>Whoopsie!</h1>\n";
        $output .= "			<p>An exception is thrown and it was not caught by the system.</p>\n";
        $output .= "			<div style=\"font-size: smaller;\">\n";

        do {
            $messageLines = explode("\n", $exception['message']);
            $message = array_shift($messageLines);

            $output .= '<h3><strong>' . $message . '</strong></h3>';

            if ($messageLines) {
                $output .= '<pre>';
                foreach ($messageLines as $messageLine) {
                    $output .= $messageLine . "\n";
                }
                $output .= '</pre>';
            }

            if ($source) {
                $fileUrl = $exception['file'];

                $sc = strrpos($fileUrl, ':');
                if ($sc !== false) {
                    $fileUrl = substr($fileUrl, 0, $sc);
                }

                $output .= '<p>The code:</p>';
                $output .= '<p><a href="file://' . $fileUrl . '">' . $exception['file'] . '</a></p>';
                $output .= '<pre>' . $source . '</pre>';
                $source = null;
            }
            $output .= '<p>The trace:</p>';
            $output .= '<pre>' . $exception['trace'] . '</pre>';

            if (isset($exception['cause'])) {
                $exception = $exception['cause'];
                $output .= "<p>Caused by:</p>";
            } else {
                $exception = null;
            }
        } while ($exception);

        $output .= "            </div>\n";
        $output .= "        </div>\n";
        $output .= "    </body>\n";
        $output .= "</html>";

        return $output;
    }

    protected function renderPlain(array $exception, $source) {
        $output = '';

        do {
            $output .= $exception['message'] . "\n";
            if ($source) {
                $output .= $exception['file'] . "\n";
                $output .= "\n" . $source . "\n\n";
                $source = null;
            }
            $output .= $exception['trace'];

            if (isset($exception['cause'])) {
                $exception = $exception['cause'];
                $output .= "\n\nCaused by:\n\n";
            } else {
                $exception = null;
            }
        } while ($exception);

        return $output;
    }

    /**
     * Parse the exception in a structured array for easy display
     * @param Exception $exception
     * @return array Array containing the values needed to display the exception
     */
    public static function getExceptionArray(Exception $exception) {
        $message = $exception->getMessage();

        $array = array();
        $array['message'] = get_class($exception) . (!empty($message) ? ': ' . $message : '');
        $array['file'] = $exception->getFile() . ':' . $exception->getLine();
        $array['trace'] = $exception->getTraceAsString();
        $array['cause'] = null;

        if ($exception instanceof ValidationException) {
            $array['message'] .= $exception->getErrorsAsString();
        }

        $cause = $exception->getPrevious();
        if (!empty($cause)) {
            $array['cause'] = self::getExceptionArray($cause);
        }

        return $array;
    }

    /**
     * Gets the source snippet where the exception has been thrown
     * @param Exception $exception
     * @param integer $offset Number of lines before and after the throw to get
     * @return array Array containing the values needed to display the exception
     */
    public static function getExceptionSource(Exception $exception, $offset = 5) {
        $source = file_get_contents($exception->getFile());
        $source = String::addLineNumbers($source);
        $source = explode("\n", $source);

        $line = $exception->getLine();

        $offsetAfter = ceil($offset / 2);
        $offsetBefore = $offset + ($offset - $offsetAfter);

        $sourceOffset = max(0, $line - $offsetBefore);
        $sourceLength = min(count($source), $line + $offsetAfter) - $sourceOffset;

        $source = array_slice($source, $sourceOffset, $sourceLength);
        $source = implode("\n", $source);

        return $source;
    }

}