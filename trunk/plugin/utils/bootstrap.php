<?php
if (!defined('LPLUGINS')) die('Access violation error!');

function load_plugin_utils() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'utils.php');
}

attach_plugin('onInitialize', 'utils', 'load_plugin_utils', 2);
