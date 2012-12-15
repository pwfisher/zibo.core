<?php

/**
 * Configuration for the Zibo bootstrap. Only the required properties should be
 * set, the others can be removed
 * @var array
 */
$config = array(
    // name of the environment (string) [required]
    'environment' => 'dev',
    'dir' => array(
        // path of the zibo.core module (string) [required]
        'core' => null,
        // path of the public directory (string) [required]
        'public' => null,
        // path of the application directory (string) [required]
        'application' => null,
        // path(s) to the module container directories (null|string|array)
        'modules' => null,
    ),
    'cache' => array(
        // flag to cache the dependencies (bool)
        'dependencies' => false,
        // flag to cache the filesystem (bool)
        'filesystem' => false,
        // flag to cache the parameters (bool)
        'parameters' => false,
    ),
    // full class name of the sapi (string)
    'sapi' => null
);