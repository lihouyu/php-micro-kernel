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

function load_plugin_dbparam() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'models'.DS.'parameter.php');

    /**
     * Load database parameters
     */
    $parameters = ActiveObject::find_all('Parameter');
    if ($parameters && sizeof($parameters) > 0) {
        foreach ($parameters as $parameter) {
            $param_val = $parameter->param_val;
            settype($param_val, $parameter->param_type);
            set_sys_param($parameter->param_key, $param_val);
        }
    }
    // Load database parameters
}

attach_event('onInitialize', 'dbparam', 'load_plugin_dbparam');
