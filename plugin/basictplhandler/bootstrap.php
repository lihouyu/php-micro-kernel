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

function load_plugin_basictplhandler() {
    $my_dir = dirname(__FILE__);
    include_once($my_dir.DS.'template.config.php');
    include_once($my_dir.DS.'tplhandler.php');

    $GLOBALS['tpl_vars'] = array();
}

function render_tpl($tpl_context) {
    $my_dir = dirname(__FILE__);

    if (sizeof($GLOBALS['tpl_vars']) > 0) {
        foreach ($GLOBALS['tpl_vars'] as $key => $var) {
            $$key = $var;
        }
    }

    $tpl_path = $GLOBALS['tpl_configs']['tpl_path'];
    $tpl_ext = $GLOBALS['tpl_configs']['tpl_ext'];

    if ($GLOBALS['tpl_configs']['use_sysconf']) {
        $tpl_path = $GLOBALS['sys_configs']['tpl_path'];
        $tpl_ext = $GLOBALS['sys_configs']['tpl_ext'];
    }

    include_once($my_dir.DS.$tpl_path.DS.$tpl_context.$tpl_ext);
}

attach_event('onInitialize', 'basictplhandler', 'load_plugin_basictplhandler');

register_signal('onTplRender');
attach_event('onTplRender', 'basictplhandler', 'render_tpl', 1);
