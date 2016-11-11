<?php
$versionFile = file(SKINNY_PATH . 'VERSION.txt');
return [
    'Skinny.version' => trim(array_pop($versionFile))
];
