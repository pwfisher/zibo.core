<?php

namespace zibo\library\html\meta;

/**
 * A HTML meta tag
 */
class Meta {

    /**
     * Name of the description meta
     * @var string
     */
    const DESCRIPTION = 'description';

    /**
     * Name of the keywords meta
     * @var string
     */
    const KEYWORDS = 'keywords';

    /**
     * Name of the meta
     * @var string
     */
    private $name;

    /**
     * Value of the meta
     * @var string
     */
    private $content;

    /**
     * Flag to see if this is a http-equiv meta or not
     * @var boolean
     */
    private $isHttpEquiv;

    /**
     * Scheme of this meta
     * @var string
     */
    private $scheme;

    /**
     * Locale of this meta
     * @var string
     */
    private $lang;

    /**
     * Constructs a new meta tag
     * @param string $name Name of the meta
     * @param string $content Value of the meta
     * @param boolean $isHttpEquiv Flag to see if this is a http-equiv meta or not
     * @param string $lang Locale of this meta
     * @param string $scheme Scheme of this meta
     * @return null
     */
    public function __construct($name, $content, $isHttpEquiv = false, $lang = null, $scheme = null) {
        $this->name = $name;
        $this->content = $content;
        $this->isHttpEquiv = $isHttpEquiv;
        $this->lang = $lang;
        $this->scheme = $scheme;
    }

    /**
     * Gets the name of the meta
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Gets the value of this meta
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Gets whether this is a http-equiv meta or not
     * @return boolean
     */
    public function isHttpEquiv() {
        return $this->isHttpEquiv;
    }

    /**
     * Gets the lang of this meta
     * @return string
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * Gets the scheme of this meta
     * @return string
     */
    public function getScheme() {
        return $this->scheme;
    }

    /**
     * Gets the HTML of this meta tag
     * @return string
     */
    public function getHtml() {
        return
            '<meta ' .
            ($this->isHttpEquiv ? 'http-equiv' : 'name') . '="' . $this->name . '" ' .
            'content="' . $this->content . '" ' .
            ($this->lang ? 'lang="' . $this->lang . '" ' : '') .
            ($this->scheme ? 'scheme="' . $this->scheme . '" ' : '') .
            '/>';
    }

}