<?php
require dirname(__DIR__) . '/config/bootstrap.php';

use Bot\Network\Server;

$server = (new Server())->startup();
