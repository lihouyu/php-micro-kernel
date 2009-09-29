<?php
if (!defined('LPLUGINS')) die('Access violation error!');

/**
 * Disable browser cache
 */
function no_cache() {
    header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
} // no_cache()
