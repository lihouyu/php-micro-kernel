<?php
/**
 * Copyright 2009 HouYu Li <karadog@gmail.com>
 *
 * This file is part of PHP Micro Kernel.
 *
 * PHP Micro Kernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Micro Kernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Micro Kernel.  If not, see <http://www.gnu.org/licenses/>.
 */

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