<?php
use Skinny\Core\Configure;

/**
 * Require the composer autoloader.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__));
define('APP_DIR', 'TestBot');
define('TESTS_DIR', 'tests');
define('APP', ROOT . DS . TESTS_DIR . DS . APP_DIR . DS);
define('CONFIG', APP . 'config' . DS);
define('TMP', APP . 'tmp' . DS);
define('MODULE_DIR', APP . 'Module' . DS . 'Modules');
define('TMP_MODULE_DIR', TMP . 'Modules');
define('SKINNY_PATH', ROOT . DS);

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'SkinnyTest\TestBot',
    'paths' => [
        'plugins' => [APP . 'plugins' . DS]
    ]
]);
