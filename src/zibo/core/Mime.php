<?php

namespace zibo\core;

use zibo\core\Zibo;

use zibo\library\filesystem\File;

/**
 * Basic MIME support
 */
class Mime {

    /**
     * Configuration key for the known MIME types
     * @var string
     */
    const PARAM_MIME = 'mime.';

    /**
     * Default MIME type
     * @var string
     */
    const MIME_UNKNOWN = 'application/octet-stream';

    /**
     * Gets the MIME type of a file based on it's extension
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @param zibo\library\filesystem\File $file The file to get the MIME from
     * @return string The MIME type of the file
     */
    public static function getMimeType(Zibo $zibo, File $file) {
        $extension = $file->getExtension();
        if (empty($extension)) {
            return self::MIME_UNKNOWN;
        }

        $mime = $zibo->getParameter(self::PARAM_MIME . $extension);
        if (!$mime) {
            $mime = self::MIME_UNKNOWN;
        }

        return $mime;
    }

}