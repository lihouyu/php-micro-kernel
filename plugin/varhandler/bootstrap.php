<?php
if (!defined('LPLUGINS')) die('Access violation error!');

function load_plugin_varhandler() {
    /**
     * Initialize session
     */
    if (intval(ini_get('session.auto_start')) == 0) {
        session_start();
    }

    if (!defined('MYHOST')) {
        define('MYHOST', $_SERVER['SERVER_NAME']);
    }
    // Initialize session

    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'var_handler.php');
}

attach_plugin('onInitialize', 'varhandler', 'load_plugin_varhandler', 5);
