<?php

declare(strict_types=1);

namespace Platine\Framework\Env;

$mock_parse_ini_string_to_false = false;
$mock_getenv_to_foo = false;

function parse_ini_string(string $ini_string, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL)
{
    global $mock_parse_ini_string_to_false;
    if ($mock_parse_ini_string_to_false) {
        return false;
    }

    return \parse_ini_string($ini_string, $process_sections, $scanner_mode);
}

function getenv(string $key)
{
    global $mock_getenv_to_foo;
    if ($mock_getenv_to_foo) {
        return 'foo';
    }

    return \getenv($key);
}

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

namespace Platine\Framework\Kernel;

$mock_current_to_false = false;

function current($a)
{
    global $mock_current_to_false;

    if ($mock_current_to_false) {
        return false;
    }

    return \current($a);
}

namespace Platine\Framework\Migration;
use Platine\Test\Framework\Fixture\MyConfig as MyConfigMigration;

$mock_app_to_config_instance = false;
$mock_app_config_items = [];

function app(string $id)
{
    global $mock_app_to_config_instance,
           $mock_app_config_items;

    if ($mock_app_to_config_instance) {
        return new MyConfigMigration($mock_app_config_items);
    }

    return \app($id);
}

namespace Platine\Framework\Template\Tag;

use Platine\Config\Config;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Session\Session;
use Platine\Test\Framework\Fixture\MyConfig;
use Platine\Test\Framework\Fixture\MyLang;
use Platine\Test\Framework\Fixture\MyRouteHelper;
use Platine\Test\Framework\Fixture\MyServerRequest;
use Platine\Test\Framework\Fixture\MySession;

$mock_app_to_instance = false;
$mock_app_lang_methods = [];
$mock_app_route_helper_methods = [];
$mock_app_server_request_methods = [];
$mock_app_session_items = [];
$mock_app_session_flash = [];
$mock_app_session_has = [];
$mock_app_config_items = [];
$mock_app_config_items = [];
$mock_sha1_foo = true;

function sha1(string $str)
{
    global $mock_sha1_foo;
    if ($mock_sha1_foo) {
        return 'foo';
    }

    return \sha1($str);
}

function app(string $id)
{
    global $mock_app_to_instance,
           $mock_app_session_items,
           $mock_app_session_has,
           $mock_app_config_items,
           $mock_app_server_request_methods,
           $mock_app_lang_methods,
           $mock_app_route_helper_methods,
           $mock_app_session_flash;

    if ($mock_app_to_instance) {
        if ($id === Config::class) {
            return new MyConfig($mock_app_config_items);
        }

        if ($id === Session::class) {
            return new MySession(
                $mock_app_session_has,
                $mock_app_session_items,
                $mock_app_session_flash
            );
        }

        if ($id === ServerRequestInterface::class) {
            return new MyServerRequest(
                $mock_app_server_request_methods
            );
        }

        if ($id === Lang::class) {
            return new MyLang(
                $mock_app_lang_methods
            );
        }

        if ($id === RouteHelper::class) {
            return new MyRouteHelper(
                $mock_app_route_helper_methods
            );
        }
    }

    return \app($id);
}

namespace Platine\Framework\Migration\Command;

$mock_date_to_sample = false;

function date(string $format)
{
    global $mock_date_to_sample;

    if ($mock_date_to_sample) {
        return '20210915_100000';
    }

    return \date($format);
}


namespace Platine\Framework\Security\JWT\Encoder;
$mock_base64_encode_to_same = false;
$mock_base64_decode_to_same = false;

function base64_encode(string $data)
{
    global $mock_base64_encode_to_same;

    if ($mock_base64_encode_to_same) {
        return $data;
    }

    return \base64_encode($data);
}

function base64_decode(string $data)
{
    global $mock_base64_decode_to_same;

    if ($mock_base64_decode_to_same) {
        return $data;
    }

    return \base64_decode($data);
}

namespace Platine\Framework\Security\JWT\Signer;
$mock_hash_hmac_algos_to_empty = false;
$mock_hash_hmac_algos_to_foo = false;
$mock_hash_hmac_to_same = false;
$mock_hash_equals_to_false = false;
$mock_hash_equals_to_true = false;

function hash_hmac_algos()
{
    global $mock_hash_hmac_algos_to_empty, $mock_hash_hmac_algos_to_foo;

    if ($mock_hash_hmac_algos_to_foo) {
        return ['foo'];
    }

    if ($mock_hash_hmac_algos_to_empty) {
        return [];
    }

    return \hash_hmac_algos();
}

function hash_hmac($algo, $data, $key, $raw_output)
{
    global $mock_hash_hmac_to_same;

    if ($mock_hash_hmac_to_same) {
        return sprintf(
            '%s|%s|%s|%s',
            $algo,
            $data,
            $key,
            $raw_output ? 'true' : 'false'
        );
    }

    return \hash_hmac($algo, $data, $key, $raw_output);
}

function hash_equals($known_string, $user_string)
{
    global $mock_hash_equals_to_false, $mock_hash_equals_to_true;

    if ($mock_hash_equals_to_false) {
        return false;
    }

    if ($mock_hash_equals_to_true) {
        return true;
    }

    return \hash_equals($known_string, $user_string);
}
