<?php

function startsWith($haystack, $needle) {
    return !strlen($needle) || (substr($haystack, 0, strlen($needle)) == $needle);
}
