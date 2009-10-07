<?php
if (!defined('LPLUGINS')) die('Access violation error!');

function run_dispatch() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'dispatcher.config.php');

    $default_module = $GLOBALS['basic_dispatcher_configs']['default_module'];
    $app_path = $GLOBALS['basic_dispatcher_configs']['app_path'];
    $file_ext = $GLOBALS['basic_dispatcher_configs']['file_ext'];

    if ($GLOBALS['basic_dispatcher_configs']['use_sysconf']) {
        $default_module = $GLOBALS['sys_configs']['default_module'];
        $app_path = $GLOBALS['sys_configs']['app_path'];
        $file_ext = $GLOBALS['sys_configs']['file_ext'];
    }
    
    $module = get_var('m', 'GP', $default_module);
    include_once($my_dir.DS.$app_path.DS.$module.$file_ext);
}

attach_plugin('onInitialize', 'basicdispatcher', 'run_dispatch', 99);
