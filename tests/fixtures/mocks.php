<?php

declare(strict_types=1);

namespace Platine\Framework\Handler\Error\Renderer;

$mock_htmlentities_to_empty = false;

function htmlentities(string $key)
{
    global $mock_htmlentities_to_empty;
    if ($mock_htmlentities_to_empty) {
        return '';
    }

    return \htmlentities($key);
}

namespace Platine\Framework\Http\Emitter;

$mock_headers_sent_to_true = false;
$mock_headers_sent_to_false = false;
$mock_ob_get_level_to_error = false;
$mock_ob_get_length_to_error = false;

function headers_sent()
{
    global $mock_headers_sent_to_true,
           $mock_headers_sent_to_false;
    if ($mock_headers_sent_to_true) {
        return true;
    }

    if ($mock_headers_sent_to_false) {
        return false;
    }

    return \headers_sent();
}

function ob_get_length()
{
    global $mock_ob_get_length_to_error;
    if ($mock_ob_get_length_to_error) {
        return 10;
    }

    return \ob_get_length();
}

function ob_get_level()
{
    global $mock_ob_get_level_to_error;
    if ($mock_ob_get_level_to_error) {
        return 10;
    }

    return \ob_get_level();
}

namespace Platine\Framework\Http\Middleware;
$mock_error_reporting_to_zero = false;

function error_reporting()
{
    global $mock_error_reporting_to_zero;
    if ($mock_error_reporting_to_zero) {
        return 0;
    }

    return \error_reporting();
}

namespace Platine\Stdlib\Helper;

$mock_realpath_to_same_param = false;

function realpath(string $name)
{
    global $mock_realpath_to_same_param;

    if ($mock_realpath_to_same_param) {
        return $name;
    }

    return \realpath($name);
}

namespace Platine\Framework\Http\Response;
$mock_time_to_1000 = false;

function time()
{
    global $mock_time_to_1000;

    if ($mock_time_to_1000) {
        return 1000;
    }

    return \time();
}
