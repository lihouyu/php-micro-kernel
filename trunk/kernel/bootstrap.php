<?php
if (!defined('LPLUGINS')) die('Access violation error!');

$kernel_dir = dirname(__FILE__);

include_once($kernel_dir.DS.'kernel.config.php');

$GLOBALS['kernel_configs']['plugin_cache_dir'] = $kernel_dir.DS.'cache';

include_once($kernel_dir.DS.'prototypes.php');
