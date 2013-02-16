<?php

namespace zibo\core\cache\classes;

use \ReflectionClass;

/**
 * Generates a single source for different class sources
 */
class ClassMinifier {

    /**
     * Regular expression to match CSS comments
     * @var string
     */
    const REGEX_COMMENT = '#/\*.*?\*/#s';

    /**
     * Generates a single source for the provided classes
     * @param array $classes Array with full class names
     * @return string
     */
    public function minify(array $classes) {
        $minified = '';

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);

            $file = $reflection->getFileName();

            $source = file_get_contents($file);
            $source = substr($source, 5); // remove <?php
            $source = preg_replace(self::REGEX_COMMENT, '', $source); // remove comments
            $source = trim($source);

            // encapsulate namespace
            if (substr($source, 0, 9) == 'namespace') {
                $positionSemiColon = strpos($source, ';');
                $source = substr($source, 0, $positionSemiColon) . ' {' . substr($source, $positionSemiColon + 1) . "\n}\n";
            }

            $minified .= $source;
        }

        return "<?php\n\n" . $minified;
    }

}