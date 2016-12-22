<?php

$config = array();

// First load yml files
foreach (glob(__DIR__ . '/**.yml') as $file) {
    $config = array_merge($config, \Spyc::YAMLLoad($file));
}

$blacklist = array(
    '/.*index.php/',
);

foreach (glob(__DIR__ . '/**.php') as $file) {
    foreach ($blacklist as $regex) {
        if (preg_match($regex, $file)) {
            continue 2;
        }
    }
    $config = array_merge($config, require($file));
}

if (isset($config['_ENV'])) {
    $_ENV = array_merge($_ENV, $config['_ENV']);
}

return $config;
