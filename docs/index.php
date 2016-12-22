<?php

// Start autoloader
require_once(__DIR__.'/../vendor/autoload.php');

// Useful tool
$accessor = new \Finwo\PropertyAccessor\PropertyAccessor();

// Fetch what we're hosting
$domain         = \Finwo\Request::domain();
$config         = require(__DIR__.'/../config/index.php');
$domainSettings = $accessor->getSafe($config, sprintf('domains|%s', $domain));

$headers = \Finwo\Request::headers();
$url     = http_build_url(array_merge(parse_url(\Finwo\Request::fulluri()), $domainSettings));

unset($headers['Host']);
unset($headers['Cookie']);

// Pre-process the headers
foreach ($headers as $name => &$header) {
    $header = sprintf("%s: %s", $name, $header);
}

// Make request
$rawResult = \Finwo\Request::transmit($url, array(
    'headers' => $headers
));

// 'Parse' result
$result        = explode("\r\n\r\n", $rawResult, 2);
$resultHeaders = explode("\r\n",array_shift($result));
$resultBody    = array_shift($result);

// Transmit headers
foreach ($resultHeaders as $resultHeader) {
    header($resultHeader);
}

// Transmit body
print($resultBody);
