<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config/core.php");
require_once("../core/router.php");

$router = new router();
$router->route($defaultController="dashboard",$defaultAction="index");

?>
