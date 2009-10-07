<?php
if (!defined('LPLUGINS')) die('Access violation error!');

function run_dispatch() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'dispatcher.config.php');

    $module = get_var('m', 'GP',
        $GLOBALS['basic_dispatcher_configs']['default_module']);
    include_once($my_dir.DS.$GLOBALS['basic_dispatcher_configs']['app_path']
        .DS.$module.$GLOBALS['basic_dispatcher_configs']['file_ext']);
}

attach_plugin('onInitialize', 'basicdispatcher', 'run_dispatch', 99);
