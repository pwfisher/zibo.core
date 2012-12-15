<?php

/**
 * Bootstrap of the Zibo test system
 */

use zibo\core\environment\filesystem\GenericFileBrowser;

use zibo\library\Autoloader;
use zibo\library\ErrorHandler;
use zibo\library\filesystem\File;

ob_start();

$rootPath = getcwd();

require_once "$rootPath/system/src/zibo/library/Autoloader.php";
require_once "$rootPath/system/src/zibo/library/ErrorHandler.php";

$errorHandler = new ErrorHandler();
$errorHandler->registerErrorHandler();

$autoloader = new Autoloader();
$autoloader->addIncludePath($rootPath . '/system/src');
$autoloader->addIncludePath($rootPath . '/system/test/src');
$autoloader->registerAutoloader();

$file = new File($rootPath, 'modules');
$files = $file->read();
foreach ($files as $file) {
    if (!$file->isDirectory()) {
        continue;
    }

    $src = new File($file, 'src');
    if ($src->exists()) {
        $autoloader->addIncludePath($src->getPath());
    }

    $src = new File($file, 'test/src');
    if ($src->exists()) {
        $autoloader->addIncludePath($src->getPath());
    }
}