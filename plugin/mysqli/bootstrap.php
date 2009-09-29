<?php
if (!defined('LPLUGINS')) die('Access violation error!');

function load_plugin_mysqli() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'mysqli.config.php');

    $global_db_configs =& $GLOBALS['db_configs'];
    $global_db_configs['table_cache_dir'] = $my_dir.DS.'cache';

    include_once($my_dir.DS.'functions.php');
    include_once($my_dir.DS.'validations.php');
    include_once($my_dir.DS.'my_db.php');
    include_once($my_dir.DS.'active_object.php');
}

function unload_plugin_mysqli() {
    MyDb::close_all();
}

attach_plugin('onInitialize', 'mysqli', 'load_plugin_mysqli', 8);
attach_plugin('onFinalize', 'mysqli', 'unload_plugin_mysqli', 8);
