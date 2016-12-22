<?php

/**
 * Include all files in the current directory
 */
foreach (glob(__DIR__.'/*.php') as $filename) {
    if ($filename==__FILE__) {
        continue;
    }
    include_once $filename;
}
