<?php


use EMicro\Application;

require_once "../vendor/autoload.php";

$application = Application::getInstance();

$handle = ($_SERVER['REQUEST_URI'] == '/' ? '/index' : $_SERVER['REQUEST_URI']);

$application->run($_SERVER['REQUEST_URI']);





