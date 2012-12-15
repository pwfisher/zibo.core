<?php

namespace zibo\library\html\meta;

/**
 * A HTML refresh meta tag
 */
class RefreshMeta extends Meta {

    /**
     * Name of the meta
     * @var string
     */
    const NAME = 'Refresh';

    /**
     * Constructs a new refresh meta tag
     * @param string $url URL to refresh to
     * @param string $seconds Number of seconds to wait before performing the refresh
     * @return null
     */
    public function __construct($url, $seconds = 3) {
        $content = $seconds . ';url=' . $url;

        parent::__construct(self::NAME, $content, true);
    }

}