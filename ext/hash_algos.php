<?php

if (!function_exists('hash_algos')) {
    function hash_algos()
    {
        return array( 'none' );
    }
}
