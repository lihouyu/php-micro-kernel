<?php
if (!defined('LPLUGINS')) die('Access violation error!');

/**
 * Assign template variables
 *
 * @param string $key Name of variable
 * @param mixed $var Value of variable
 */
function assign($key, $var) {
    $GLOBALS['tpl_vars'][$key] = $var;
}
