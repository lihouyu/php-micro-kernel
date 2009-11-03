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
 * Singal event holder
 */
$GLOBALS['_signals'] = array(
    'onInitialize' => array(),
    'onFinalize' => array()
);

$GLOBALS['_lazy_signals'] = array();

/**
 *
 * Event parameter holder
 */
$GLOBALS['_event_params'] = array();

/**
 * Attach plugin entry to event signal
 *
 * @param string $event The signal name plugin attached to
 * @param string $name The name of the plugin
 * @param string $entry_func The name of the entry function
 * @param integer $priority Plugin load priority
 */
function attach_event($signal, $name, $entry_func, $priority = 10) {
    $global__signals =& $GLOBALS['_signals'];
    if (isset($global__signals[$signal])) {
        if (!isset($global__signals[$signal][$priority]))
            $global__signals[$signal][$priority] = array();
        $global__signals[$signal][$priority][] = array($name, $entry_func);
    } else {
        $global__lazy_signals =& $GLOBALS['_lazy_signals'];
        if (!isset($global__lazy_signals[$signal])) {
            $global__lazy_signals[$signal] = array();
            $global__lazy_signals[$signal][$priority] = array();
        }
        $global__lazy_signals[$signal][$priority][] = array($name, $entry_func);
    }
} // attach_event($signal, $name, $entry_func, $priority)

/**
 * Load plugins attached to a specified signal
 *
 * @param string $signal The signal name
 */
function raise_signal($signal) {
    $global__signals =& $GLOBALS['_signals'];
    $global__event_params =& $GLOBALS['_event_params'];
    if (isset($global__signals[$signal])) {
        $signal_plugins = $global__signals[$signal];
        if (sizeof($signal_plugins) > 0) {
            ksort($signal_plugins);
            foreach ($signal_plugins as $priority => $plugin_metas) {
                if (sizeof($plugin_metas) == 0) continue;
                foreach ($plugin_metas as $plugin_meta) {
                    $plugin_entry_func = $plugin_meta[1];
                    if (@isset($global__event_params[$signal][$plugin_entry_func])) {
                        $plugin_entry_func($global__event_params[$signal][$plugin_entry_func]);
                    } else {
                        $plugin_entry_func();
                    }
                }
            }
        }
    }
} // raise_signal($signal)

/**
 * Register new signals in plugins
 *
 * @param string $signal The signal name
 */
function register_signal($signal) {
    $global__signals =& $GLOBALS['_signals'];
    if (!isset($global__signals[$signal])) {
        $global__lazy_signals =& $GLOBALS['_lazy_signals'];
        if (isset($global__lazy_signals[$signal])) {
            $global__signals[$signal] = $global__lazy_signals[$signal];
        } else {
            $global__signals[$signal] = array();
        }
    }
} // register_signal($signal)

/**
 * Bind event parameters
 *
 * @param string $signal The signal name
 * @param string $event_func The name of the event function
 * @param mixed $params Parameters to be passed to event function
 */
function bind_param($signal, $event_func, &$params) {
    $global__event_params =& $GLOBALS['_event_params'];
    if (!isset($global__event_params[$signal])) {
        $global__event_params[$signal] = array();
    }
    $global__event_params[$signal][$event_func] =& $params;
} // bind_param($signal, $event_func, &$params)
