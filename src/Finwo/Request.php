<?php

namespace Finwo;

/**
 * Class Request
 *
 * Handles some minor common functions
 */
class Request
{
    static function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    static function fulluri()
    {
        return http_build_url(array(
            'scheme' => Request::scheme(),
            'host'   => Request::domain(),
            'path'   => Request::uri(),
        ));
    }

    static function safeuri()
    {
        return urlencode(Request::fulluri());
    }

    static function scheme()
    {
        static $cache = null;
        if (is_null($cache)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                $cache = $_SERVER['HTTP_X_FORWARDED_PROTO'];
            } elseif (isset($_SERVER['REQUEST_SCHEME'])) {
                $cache = $_SERVER['REQUEST_SCHEME'];
            } elseif (intval($_SERVER['SERVER_PORT']) == 443) {
                $cache = 'https';
            } else {
                $cache = 'http';
            }
        }

        return $cache;
    }

    static function domain()
    {
        static $cache = null;
        if (is_null($cache)) {
            if (isset($_SERVER['HTTP_HOST'])) {
                $cache = $_SERVER['HTTP_HOST'];
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $cache = $_SERVER['SERVER_NAME'];
            } else {
                $cache = 'islive.nl';
            }
        }

        return $cache;
    }

    /**
     * Transmits a request to another host
     *
     * If returning response, beware that it's raw
     * (includes headers, transfer encoding, etc)
     *
     * @param       $url
     * @param array $options
     *
     * @return bool|string
     */
    static function transmit($url, $options = array())
    {
        // Default options
        $options = array_merge(array(
            'return'  => true,
            'method'  => 'GET',
            'headers' => array(),
            'body   ' => null,
        ), $options);

        // Disect the url
        $parts = parse_url($url);

        // Convert the body if needed
        if (in_array(gettype($options['body']), array( 'array', 'object' ))) {
            $options['body'] = http_build_query($options['body']);
            array_push($options['headers'], "Content-Type: application/x-www-form-urlencoded");
        }

        // Open a connection to the target host
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
        if (!$fp) {
            return false;
        }

        // Construct message to the host
        $out = strtoupper($options['method']) . " " . $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '') . " HTTP/1.0\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        if (count($options['headers'])) {
            $out .= \implode("\r\n", $options['headers']) . "\r\n";
        }
        $out .= is_string($options['body']) ? sprintf("Content-Length: %s\r\n", strlen($options['body'])) : '';
        $out .= "Connection: Close\r\n\r\n";
        $out .= is_string($options['body']) ? $options['body'] : '';

        // Send request
        fwrite($fp, $out);

        // What we'll return
        $result = true;

        // Return data if asked to
        if ($options['return']) {
            $result = stream_get_contents($fp);
        }

        // Close the socket again
        fclose($fp);

        // Return answer
        return $result;
    }

    /**
     * Returns the request headers
     *
     * @return array
     */
    static function headers()
    {
        // Fetch given headers
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (startsWith($name, 'HTTP_')) {
                $name = explode('_', $name);
                array_shift($name);
                $name           = implode('-', array_map(function ($element) {
                    return ucfirst(strtolower($element));
                }, $name));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
