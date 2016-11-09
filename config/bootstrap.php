<?php
/**
 * Configure paths required to find general filepath constants.
 */
require __DIR__ . DIRECTORY_SEPARATOR . 'paths.php';

/**
 * Use composer to load the autoloader.
 */
require ROOT . DS . 'vendor' . DS . 'autoload.php';

use Bot\Configure\Configure;

/**
 * Read configuration file and inject configuration into various
 * Mars classes.
 *
 * By default there is only one configuration file. It is often a good
 * idea to create multiple configuration files, and separate the configuration
 * that changes from configuration that does not. This makes deployment simpler.
 */
try {
    Configure::load('config');
    Configure::load('commands');
} catch (\Exception $e) {
    die($e->getMessage() . "\n");
}

/**
 * Set server timezone to UTC. You can change it to another timezone of your
 * choice but using UTC makes time calculations / conversions easier.
 */
date_default_timezone_set('UTC');

/**
 * Set time limit to unlimited or the script will ended itself.
 */
set_time_limit(0);


/**
 * Set the memory unlimited.
 */
ini_set('memory_limit', '-1');
