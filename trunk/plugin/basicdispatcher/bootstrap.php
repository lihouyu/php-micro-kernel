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

attach_event('onInitialize', 'basicdispatcher', 'run_dispatch', 99);
