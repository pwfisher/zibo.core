<?php

namespace zibo\library\xml\dom;

use zibo\library\filesystem\File;
use zibo\library\xml\exception\LibXmlException;

use \DOMDocument;

/**
 * Overriden DOMDocument with more validation options
 */
class Document extends DOMDocument {

    /**
     * Schema file
     * @var string
     */
    private $schemaFile;

    /**
     * Relax NG file
     * @var string
     */
    private $relaxNGFile;

    /**
     * Set the file name of the validation schema for this document
     * @param string $fileName
     * @return null
     */
    public function setSchemaFile($fileName) {
        $this->schemaFile = $fileName;
    }

    /**
     * Set the file name of the validation Relax NG for this document
     * @param string $fileName
     * @return null
     */
    public function setRelaxNGFile($fileName) {
        $this->relaxNGFile = $fileName;
    }

    /**
     * Loads an XML document from a file
     * @param string $fileName path to the XML document
     * @param int $options Bitwise OR of the libxml option constants
     * @return boolean true when the file is loaded
     * @throws zibo\library\xml\exception\LibXmlException when the file could
     * not be loaded or validated
     */
    public function load($fileName, $options = 0) {
        $this->initErrorHandling();

        if (!parent::load($fileName, $options)) {
            $this->throwError('Failed loading the XML', $fileName);
        }

        $this->validateDocument($fileName);
        $this->clearErrorHandling();

        return true;
    }

    /**
     * Loads an XML document from a string
     * @param string $source string containing the XML
     * @param int $options BitwiseOR of the libxml option constants
     * @return boolean true when the source is loaded
     * @throws zibo\library\xml\exception\LibXmlException when the source could
     * not be loaded or validated
     */
    public function loadXML($source , $options = 0) {
        $this->initErrorHandling();

        if (!parent::loadXML($source, $options)) {
            $this->throwError('Failed loading the XML', $source);
        }

        $this->validateDocument($source);
        $this->clearErrorHandling();

        return true;
    }

    /**
     * Validate the loaded document to the set schema and/or Relax NG files
     * @param string $source source of the document of file name of the source,
     * needed for the exception if thrown
     * @return null
     * @throws zibo\library\xml\exception\LibXmlException when the document is
     * not validated by a set validation schema
     */
    private function validateDocument($source) {
        if ($this->schemaFile && !$this->schemaValidate($this->schemaFile)) {
            $this->throwError('Failed validating the XML', $source);
        }

        if ($this->relaxNGFile && !$this->relaxNGValidate($this->relaxNGFile)) {
            $this->throwError('Failed validating the XML', $source);
        }
    }

    /**
     * Throw a exception caused by PHP's internal XML library
     * @param string $message message for the exception
     * @param string $source source of the document of file name of the source
     * @return null
     * @throws zibo\library\xml\exception\LibXmlException
     */
    private function throwError($message, $source = null) {
        $errors = libxml_get_errors();

        $this->clearErrorHandling();

        throw new LibXmlException($message, 0, $errors, $source);
    }

    /**
     * Initialize the libxml error handling
     * @return null
     */
    private function initErrorHandling() {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
    }

    /**
     * Clear the libxml error handling
     * @return null
     */
    private function clearErrorHandling() {
        libxml_clear_errors();
        libxml_use_internal_errors(false);
    }

}