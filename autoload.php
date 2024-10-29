<?php
spl_autoload_register('ebdn_plugin_autoload');


function ebdn_plugin_autoload($class)
{
    $prefix = 'Dnolbon\\';
    $baseDir = __DIR__ . '/src/';

    if (strpos($class, $prefix) === false) {
        return;
    }

    $file = $baseDir . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}
