<?php
if (!defined('LPLUGINS')) die('Access violation error!');

/**
 * We do not need magic quotes.
 * If it's turned on, then strip these slashes.
 */
function stripslashes_deep($var) {
    $var = is_array($var)?
        array_map('stripslashes_deep', $var):
        stripslashes($var);
    return $var;
}

if (get_magic_quotes_gpc()) {
    if (isset($_GET) && !empty($_GET)) {
        $_GET = stripslashes_deep($_GET);
    }
    if (isset($_POST) && !empty($_POST)) {
        $_POST = stripslashes_deep($_POST);
    }
    if (isset($_COOKIE) && !empty($_COOKIE)) {
        $_COOKIE = stripslashes_deep($_COOKIE);
    }
}
// Strip slashes

/**
 * Prepare all plugins
 */
$plugins = scandir(PLUGIN);
if (sizeof($plugins) > 0) {
	foreach ($plugins as $plugin) {
		if ($plugin[0] == '.' || !is_dir(PLUGIN.DS.$plugin)) continue;
		include_once(PLUGIN.DS.$plugin.DS.'bootstrap.php');
	}
}
// Prepare all plugins