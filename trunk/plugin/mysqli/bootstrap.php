<?php
/**
 * Copyright 2009 HouYu Li <karadog@gmail.com>
 *
 * This file is part of LoadingPlugins.
 *
 * LoadingPlugins is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * LoadingPlugins is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LoadingPlugins.  If not, see <http://www.gnu.org/licenses/>.
 */

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

attach_event('onInitialize', 'mysqli', 'load_plugin_mysqli', 8);
attach_event('onFinalize', 'mysqli', 'unload_plugin_mysqli', 8);
