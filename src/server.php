<?php

/**
 * Main script for a Zibo web application server (experimental)
 */

/**
 * Path to the Zibo bootstrap configuration file
 * @var string
 */
const ZIBO_CONFIG = 'bootstrap.config.php';

try {
    if (!file_exists(ZIBO_CONFIG)) {
        $path = realpath(dirname(ZIBO_CONFIG));
        throw new Exception('No configuration file exists, please copy zibo.core/src/bootstrap.config.php to ' . $path . '.');
    }
    include_once ZIBO_CONFIG;

    if (!isset($config['dir']['core'])) {
        throw new Exception('The directory of the zibo.core module is not set, please check your configuration in ' . ZIBO_CONFIG . '.');
    }

    $bootstrap = $config['dir']['core'] . '/src/zibo/core/Bootstrap.php';
    if (!file_exists($bootstrap)) {
        throw new Exception('The directory of the zibo.core module is not correct, please check your configuration in ' . ZIBO_CONFIG . '.');
    }
    require_once $bootstrap;

    $config['sapi'] = 'zibo\\core\\environment\\sapi\\ServerSapi';

    $bootstrap = new zibo\core\Bootstrap($config);
    $zibo = $bootstrap->boot();

    $server = new zibo\core\server\Server($zibo);
    $server->service();
} catch (Exception $exception) {
    $view = null;
    if (isset($bootstrap) && !is_string($bootstrap)) {
        $view = $bootstrap->createExceptionView($exception);
    }

    if (PHP_SAPI != 'cli') {
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        header($protocol . ' 500 Internal Server Error');

        if ($view) {
            $view->setIsHtml(true);
        }
    }

    if ($view) {
        echo $view->render();
    } else {
        echo 'Fatal error: ' . $exception->getMessage() . "\n";
    }
}