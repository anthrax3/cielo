<?php

/**
 * Cielo
 *
 * @package  cielo
 * @author   Ramon Barros <contato@ramon-barros.com>
 */

ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('America/Sao_Paulo');

define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', realpath(__DIR__.DS.'..'));
define('EXT', '.php');
$time = time();
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $time).'GMT');
session_start();
/*
| ------------------------------------------------- -------------------------
| Check Extensions
| ------------------------------------------------- -------------------------
|
| The application requires some extensions to work
| And we will see if they are loaded.
|
*/

if (! extension_loaded('curl')) {
    die('cielo requires the cUrl PHP extension.'.PHP_EOL);
    exit(1);
}

if (! extension_loaded('ionCube Loader')) {
    //die('cielo requires the ionCube PHP Loader extension.'.PHP_EOL);
    //exit(1);
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| This application is installed by the Composer,
| that provides a class loader automatically.
| Use it to seamlessly and feel free to relax.
|
*/

$composer_autoload = APP_ROOT . DS . 'vendor' . DS . 'autoload.php';
if (!file_exists($composer_autoload)) {
    die('Please use the composer to install http://getcomposer.org');
}
require $composer_autoload;
