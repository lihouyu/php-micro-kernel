<?php
if (!defined('LPLUGINS')) die('Access violation error!');

/**
 * Set system global configuration parameters
 *
 * @param string $key The parameter name
 * @param mixed $val The parameter value
 */
function set_sys_param($key, $val) {
    $global_sys_configs =& $GLOBALS['sys_configs'];
    $global_sys_configs[$key] = $val;
} // set_sys_param($key, $val)
