<?php

/**
 * Define when the script has started.
 */
define('TIME_START', microtime(true));

/**
 * Use the DS to separate the directories in other defines.
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * The full path to the directory which holds "App", WITHOUT a trailing DS.
 */
define('ROOT', dirname(__DIR__));

/**
 * The actual directory name for the "App".
 */
define('APP_DIR', 'src');

/**
 * Path to the application's directory.
 */
define('APP', ROOT . DS . APP_DIR . DS);

/**
 * Path to the config directory.
 */
define('CONFIG', ROOT . DS . 'config' . DS);

/**
* Path to the temporary files directory.
*/
define('TMP', ROOT . DS . 'tmp' . DS);

/**
 * Path to the Module directory.
 */
define('MODULE_DIR', APP . 'Module' . DS . 'Modules');

/**
 * Path to the tmp Module directory.
 */
define('TMP_MODULE_DIR', TMP . 'Modules');
