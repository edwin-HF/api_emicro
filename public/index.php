<?php


use EMicro\Application;
use EMicro\Config;
use EMicro\Loader;

require_once "../vendor/autoload.php";

define("APP_PATH", dirname(__DIR__).'/application');
define("CONFIG_PATH", APP_PATH.'/config');

Loader::scan(APP_PATH);
Config::scan(CONFIG_PATH);
Application::scan(APP_PATH);

$handle = ($_SERVER['REQUEST_URI'] == '/' ? '/index' : $_SERVER['REQUEST_URI']);

Application::run($handle);






