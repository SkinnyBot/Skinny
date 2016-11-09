<?php
$versionFile = file(ROOT . DS . 'VERSION.txt');
$config['Bot.version'] = trim(array_pop($versionFile));
return $config;
