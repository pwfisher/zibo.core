<?php

/**
 * Main script for the Zibo console
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

    $bootstrap = new zibo\core\Bootstrap($config);
    $zibo = $bootstrap->boot();

    $console = new zibo\core\console\Console($zibo);

    if (in_array('--debug', $_SERVER['argv']) !== false) {
        foreach ($_SERVER['argv'] as $index => $value) {
            if ($value == '--debug') {
                unset($_SERVER['argv'][$index]);
            }
        }

    	$console->setIsDebug(true);
    }

    $output = $zibo->getDependency('zibo\\core\\console\\output\\Output');

    if (in_array('--shell', $_SERVER['argv']) != false) {
        foreach ($_SERVER['argv'] as $index => $value) {
            if ($value == '--shell') {
                unset($_SERVER['argv'][$index]);
            }
        }

        $input = $zibo->getDependency('zibo\\core\\console\\input\\Input', 'shell');
        $output->write('Zibo ' . zibo\core\Zibo::VERSION . ' console (' . $zibo->getEnvironment()->getName() . ').');
        $output->write('Type \'help\' to get you started.');
    } else {
        $input = $zibo->getDependency('zibo\\core\\console\\input\\Input', 'command');
    }

    $console->setInput($input);
    $console->setOutput($output);
    $console->run();
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