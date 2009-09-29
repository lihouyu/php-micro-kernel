<?php
define('LPLUGINS', 1);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

define('PLUGIN', ROOT.DS.'plugin');

include_once(ROOT.DS.'kernel'.DS.'bootstrap.php');
include_once(ROOT.DS.'init.php');

raise_signal('onInitialize');

raise_signal('onFinalize');
