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

function load_plugin_utils() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'utils.php');
}

attach_plugin('onInitialize', 'utils', 'load_plugin_utils', 2);
