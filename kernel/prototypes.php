<?php
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
 * Attach plugin entry to event signal
 *
 * @param string $event The signal name plugin attached to
 * @param string $name The name of the plugin
 * @param string $entry_func The name of the entry function
 * @param integer $priority plugin load priority
 */
function attach_plugin($signal, $name, $entry_func, $priority = 10) {
    $global__signals =& $GLOBALS['_signals'];
    if (isset($global__signals[$signal])) {
        $global__signals[$signal][$priority] = array($name, $entry_func);
    } else {
        $global__lazy_signals =& $GLOBALS['_lazy_signals'];
        if (!isset($global__lazy_signals[$signal]))
            $global__lazy_signals[$signal] = array();
        $global__lazy_signals[$signal][$priority] = array($name, $entry_func);
    }
} // attach_plugin($event, $name, $entry_func, $priority)

/**
 * Load plugins attached to a specified signal
 *
 * @param string $signal The signal name
 */
function raise_signal($signal) {
    $global__signals =& $GLOBALS['_signals'];
    if (isset($global__signals[$signal])) {
        $signal_plugins = $global__signals[$signal];
        if (sizeof($signal_plugins) > 0) {
            ksort($signal_plugins);
            foreach ($signal_plugins as $priority => $plugin_meta) {
                $plugin_entry_func = $plugin_meta[1];
                $plugin_entry_func();
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
