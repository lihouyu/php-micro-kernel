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

/**
 * Registered role actions
 */
$GLOBALS['_acl_actions'] = array();

/**
 * Register an action which can be assinged to roles
 *
 * @param string $act_name The unix name style name of the action
 * @param string $act_text A descriptive phrase
 */
function register_action($act_name, $act_text) {
    $global__acl_actions =& $GLOBALS['_acl_actions'];
    $global__acl_actions[$act_name] = $act_text;
}