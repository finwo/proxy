<?php

if (!function_exists('hash')) {
    function hash($algo, $input, $raw_output = false)
    {
        if (!is_string($input)) {
            $input = http_build_query($input);
        }
        $hash = 0;
        if (!strlen($input)) {
            return $hash;
        }
        $input = str_split($input);
        while (count($input)) {
            $character = ord(array_shift($input));
            $hash      = (($hash << 5) - $hash) + $character;
        }

        return $raw_output ? $hash : dechex($hash);
    }
}
