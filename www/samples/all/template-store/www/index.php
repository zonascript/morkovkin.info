<?php

error_reporting(E_ALL);

define('DEBUG', $_SERVER['SERVER_ADDR'] === '127.0.0.1');
define('ROOT_DIR', rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '\\/'));
define('ROOT_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/'));

require_once(ROOT_DIR . '/app/classes/cf.php');

cf::import(ROOT_DIR . '/app/classes/core');
cf::import(ROOT_DIR . '/app/classes/modules');
cf::import(ROOT_DIR . '/app/classes/systems');

$config = include(ROOT_DIR . '/app/config.inc');

if (DEBUG) 
    $config = cf::mergeArray($config, include(ROOT_DIR . '/app/config.dbg.inc'));

cf::run($config);