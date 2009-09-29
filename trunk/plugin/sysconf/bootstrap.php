<?php
if (!defined('LPLUGINS')) die('Access violation error!');

function load_plugin_sysconf() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'sysconf.config.php');
    include_once($my_dir.DS.'sysconf.php');
}

attach_plugin('onInitialize', 'sysconf', 'load_plugin_sysconf', 3);
