<?php

declare(strict_types=1);

namespace Platine\Framework\Tool\Database;

$mock_function_exists_to_false = false;
$mock_function_exists_to_true = false;
$mock_gzencode_to_value = false;
$mock_gzdecode_to_value = false;
function function_exists($string): bool
{
    global $mock_function_exists_to_false,
           $mock_function_exists_to_true;
    if ($mock_function_exists_to_false) {
        return false;
    } elseif ($mock_function_exists_to_true) {
        return true;
    }

    return \function_exists($string);
}

function gzencode($string): string
{
    global $mock_gzencode_to_value;
    if ($mock_gzencode_to_value) {
        return $mock_gzencode_to_value;
    }

    return \gzencode($string);
}

function gzdecode($string): string
{
    global $mock_gzdecode_to_value;
    if ($mock_gzdecode_to_value) {
        return $mock_gzdecode_to_value;
    }

    return \gzdecode($string);
}

namespace Platine\Upload\Util;

$mock_tempnam_to_value = false;
function tempnam($string, $prefix): string
{
    global $mock_tempnam_to_value;
    if ($mock_tempnam_to_value) {
        return $mock_tempnam_to_value;
    }

    return \tempnam($string, $prefix);
}

namespace Platine\Upload\Storage;

$mock_realpath_to_value = false;
function realpath($string): string
{
    global $mock_realpath_to_value;
    if ($mock_realpath_to_value) {
        return $string;
    }

    return \realpath($string);
}


namespace Platine\Stdlib\Helper;

$mock_str_pad_to_value = false;
function str_pad($string, $max, $pad_string, $min): string
{
    global $mock_str_pad_to_value;
    if ($mock_str_pad_to_value) {
        return $mock_str_pad_to_value;
    }

    return \str_pad($string, $max, $pad_string, $min);
}


namespace Platine\Framework\Security\OTP;

$mock_random_int = false;

function random_int(int $min, int $max): int
{
    global $mock_random_int;
    if ($mock_random_int) {
        return 1;
    }

    return \random_int($min, $max);
}



namespace Platine\Framework\Http\RateLimit\Storage;

$mock_extension_loaded_to_false = false;
$mock_extension_loaded_to_true = false;
$mock_ini_get_to_false = false;
$mock_ini_get_to_true = false;
$mock_apcu_fetch_to_false = false;
$mock_apcu_store_to_false = false;
$mock_apcu_store_to_true = false;
$mock_apcu_delete_to_false = false;
$mock_apcu_delete_to_true = false;
$mock_apcu_exists_to_false = false;
$mock_apcu_exists_to_true = false;


function apcu_exists($key): bool
{
    global $mock_apcu_exists_to_false, $mock_apcu_exists_to_true;
    if ($mock_apcu_exists_to_false) {
        return false;
    } elseif ($mock_apcu_exists_to_true) {
        return true;
    }

    return false;
}

/**
 * @return null|string
 */
function apcu_fetch($key, bool &$success)
{
    global $mock_apcu_fetch_to_false;
    if ($mock_apcu_fetch_to_false) {
        $success = false;
    } else {
        $success = true;
        return 6;
    }
}

function apcu_store($key, $var, int $ttl = 0): bool
{
    global $mock_apcu_store_to_false, $mock_apcu_store_to_true;
    if ($mock_apcu_store_to_false) {
        return false;
    } elseif ($mock_apcu_store_to_true) {
        return true;
    }

    return false;
}

function apcu_delete($key): bool
{
    global $mock_apcu_delete_to_false, $mock_apcu_delete_to_true;
    if ($mock_apcu_delete_to_false) {
        return false;
    } elseif ($mock_apcu_delete_to_true) {
        return true;
    }

    return false;
}

function extension_loaded(string $name): bool
{
    global $mock_extension_loaded_to_false, $mock_extension_loaded_to_true;
    if ($mock_extension_loaded_to_false) {
        return false;
    } elseif ($mock_extension_loaded_to_true) {
        return true;
    } else {
        return \extension_loaded($name);
    }
}

/**
 * @return bool|string
 */
function ini_get(string $option)
{
    global $mock_ini_get_to_true, $mock_ini_get_to_false;
    if ($mock_ini_get_to_false) {
        return false;
    } elseif ($mock_ini_get_to_true) {
        return true;
    } else {
        return \ini_get($option);
    }
}

namespace Platine\Framework\Task;

$mock_preg_split_to_false = false;

function preg_split(string $pattern, string $subject, int $limit = -1, int $flags = 0)
{
    global $mock_preg_split_to_false;
    if ($mock_preg_split_to_false) {
        return false;
    }

    return \preg_split($pattern, $subject, $limit, $flags);
}


namespace Platine\Framework\Auth\Authentication;

$mock_session_unset = false;
$mock_session_destroy = false;

function session_unset()
{
    global $mock_session_unset;
    if ($mock_session_unset) {
        return true;
    }

    return \session_unset();
}

function session_destroy()
{
    global $mock_session_destroy;
    if ($mock_session_destroy) {
        return true;
    }

    return \session_destroy();
}


namespace Platine\Framework\Security;

$mock_base64_encode_to_sample = false;

function base64_encode(string $string)
{
    global $mock_base64_encode_to_sample;
    if ($mock_base64_encode_to_sample) {
        return 'nonce';
    }

    return \base64_encode($string);
}


namespace Platine\Framework\Security\Policy;

$mock_base64_decode_to_false = false;

function base64_decode(string $string, bool $strict = false)
{
    global $mock_base64_decode_to_false;
    if ($mock_base64_decode_to_false) {
        return false;
    }

    return \base64_decode($string, $strict);
}


namespace Platine\Framework\Env;

$mock_parse_ini_string_to_false = false;
$mock_getenv_to_foo = false;
$mock_preg_replace_callback_to_null = false;

function preg_replace_callback($pattern, callable $callback, $subject, int $limit = -1, &$count = null, int $flags = 0)
{
    global $mock_preg_replace_callback_to_null;
    if ($mock_preg_replace_callback_to_null) {
        return null;
    }

    return \preg_replace_callback($pattern, $callback, $subject, $limit, $count, $flags);
}


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

namespace Platine\Framework\Task;
$mock_time_to_1000000000 = false;

function time()
{
    global $mock_time_to_1000000000;

    if ($mock_time_to_1000000000) {
        return 1000000000;
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
use Platine\Test\Framework\Fixture\MyConfig;
$mock_app_to_config_instance = false;
$mock_app_config_items = [];

function app(string $id)
{
    global $mock_app_to_config_instance,
           $mock_app_config_items;

    if ($mock_app_to_config_instance) {
        return new MyConfig($mock_app_config_items);
    }

    return \app($id);
}

namespace Platine\Framework\Security\Csrf;

$mock_sha1_foo = true;

function sha1(string $str)
{
    global $mock_sha1_foo;
    if ($mock_sha1_foo) {
        return 'foo';
    }

    return \sha1($str);
}


namespace Platine\Framework\Http\Action;

use Platine\Test\Framework\Fixture\MyApp;
use Platine\Filesystem\Filesystem;

$mock_app_httpaction_to_instance = false;
function app(?string $id = null)
{
    global $mock_app_httpaction_to_instance;
    if ($mock_app_httpaction_to_instance) {
        if ($id === null) {
            $app = new MyApp();
            $app->bind(Filesystem::class);

            return $app;
        }
    }

    return \app($id);
}

namespace Platine\Framework\Form\Param;

use Platine\Http\ServerRequestInterface;
use Platine\Test\Framework\Fixture\MyServerRequest;

$mock_app_form_to_instance = false;
$mock_app_form_server_request_methods = [];
function app(string $id)
{
    global $mock_app_form_to_instance, $mock_app_form_server_request_methods;
    if ($mock_app_form_to_instance) {
        if ($id === ServerRequestInterface::class) {
            return new MyServerRequest(
                $mock_app_form_server_request_methods
            );
        }
    }

    return \app($id);
}

namespace Platine\Framework\Template\Tag;

use Platine\Config\Config;
use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\AuthorizationInterface;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Route\Router;
use Platine\Session\Session;
use Platine\Test\Framework\Fixture\MyConfig;
use Platine\Test\Framework\Fixture\MyCsrfManager;
use Platine\Test\Framework\Fixture\MyLang;
use Platine\Test\Framework\Fixture\MyRouteHelper;
use Platine\Test\Framework\Fixture\MyRouter;
use Platine\Test\Framework\Fixture\MyServerRequest;
use Platine\Test\Framework\Fixture\MySession;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Helper\FileHelper;


$mock_app_to_instance = false;
$mock_app_auth_object = null;
$mock_app_csrfmanager_object = null;
$mock_app_filesystem_object = null;
$mock_app_filehelper_object = null;
$mock_app_lang_methods = [];
$mock_app_route_helper_methods = [];
$mock_app_server_request_methods = [];
$mock_app_router_methods = [];
$mock_app_session_items = [];
$mock_app_session_flash = [];
$mock_app_session_has = [];
$mock_app_config_items = [];


function app(?string $id)
{
    global $mock_app_to_instance,
           $mock_app_session_items,
           $mock_app_session_has,
           $mock_app_config_items,
           $mock_app_server_request_methods,
           $mock_app_lang_methods,
           $mock_app_csrfmanager_object,
           $mock_app_filesystem_object,
           $mock_app_filehelper_object,
           $mock_app_route_helper_methods,
           $mock_app_session_flash,
           $mock_app_router_methods,
           $mock_app_auth_object;

    if ($mock_app_to_instance) {
        if ($id === AuthenticationInterface::class || $id === AuthorizationInterface::class) {
            return $mock_app_auth_object;
        }

        if ($id === Config::class) {
            return new MyConfig($mock_app_config_items);
        }

        if ($id === CsrfManager::class && $mock_app_csrfmanager_object !== null) {
            return $mock_app_csrfmanager_object;
        }

        if ($id === Filesystem::class && $mock_app_filesystem_object !== null) {
            return $mock_app_filesystem_object;
        }

        if ($id === FileHelper::class && $mock_app_filehelper_object !== null) {
            return $mock_app_filehelper_object;
        }

        if ($id === Router::class) {
            return new MyRouter($mock_app_router_methods);
        }

        if ($id === CsrfManager::class) {
            return new MyCsrfManager($mock_app_config_items);
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

namespace Platine\Framework\Migration\Seed\Command;

$mock_date_to_sample_seed = false;

function date(string $format)
{
    global $mock_date_to_sample_seed;

    if ($mock_date_to_sample_seed) {
        return '20210915_100000';
    }

    return \date($format);
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


namespace Platine\Framework\Helper;

$mock_file_exists_to_false = false;
$mock_fopen_to_false = false;
$mock_extension_loaded_to_false = false;
$mock_imagerotate_to_false = false;
$mock_imagecolorallocatealpha_to_false = false;
$mock_imagecolorallocate_to_false = false;
$mock_getimagesize_to_value = false;
$mock_hexdec_to_value = false;
$mock_getimagesize_to_false = false;
$mock_imagecreatefrompng_to_false = false;
$mock_stream_socket_client_to_value = false;
$mock_stream_socket_client_to_false = false;
$mock_fgets_to_value = false;
$mock_fgets_to_false = false;
$mock_fread_to_value = false;
$mock_fread_to_false = false;

function hexdec($hex_string)
{
    global $mock_hexdec_to_value;
    if ($mock_hexdec_to_value) {
        return $mock_hexdec_to_value;
    }

    return \hexdec($hex_string);
}
function imagerotate($image, $angle, $background_color)
{
    global $mock_imagerotate_to_false;
    if ($mock_imagerotate_to_false) {
        return false;
    }

    return \imagerotate($image, $angle, $background_color);
}

function imagecolorallocate($image, $red, $green, $blue)
{
    global $mock_imagecolorallocate_to_false;
    if ($mock_imagecolorallocate_to_false) {
        return false;
    }

    return \imagecolorallocate($image, $red, $green, $blue);
}

function imagecolorallocatealpha($image, $red, $green, $blue, $alpha)
{
    global $mock_imagecolorallocatealpha_to_false;
    if ($mock_imagecolorallocatealpha_to_false) {
        return false;
    }

    return \imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
}

function extension_loaded($ext)
{
    global $mock_extension_loaded_to_false;
    if ($mock_extension_loaded_to_false) {
        return false;
    }

    return \extension_loaded($ext);
}

function imagecreatefrompng($file)
{
    global $mock_imagecreatefrompng_to_false;
    if ($mock_imagecreatefrompng_to_false) {
        return false;
    }

    return \imagecreatefrompng($file);
}

function getimagesize($file)
{
    global $mock_getimagesize_to_value,
            $mock_getimagesize_to_false;

    if ($mock_getimagesize_to_false) {
        return false;
    }

    if ($mock_getimagesize_to_value) {
        return $mock_getimagesize_to_value;
    }

    return \getimagesize($file);
}

function fread($stream, ?int $length = null)
{
    global $mock_fread_to_false,
           $mock_fread_to_value;

    if ($mock_fread_to_false) {
        return false;
    }

    if ($mock_fread_to_value) {
        return $mock_fread_to_value;
    }

    return \fread($stream, $length);
}

function fgets($stream, ?int $length = null)
{
    global $mock_fgets_to_false,
           $mock_fgets_to_value;

    if ($mock_fgets_to_false) {
        return false;
    }

    if ($mock_fgets_to_value) {
        return $mock_fgets_to_value;
    }

    return \fgets($stream, $length);
}

function stream_socket_client(string $address, ?int &$error_code = null, ?string &$error_message = null)
{
    global $mock_stream_socket_client_to_value,
           $mock_stream_socket_client_to_false;

    if ($mock_stream_socket_client_to_false) {
        return false;
    }

    if ($mock_stream_socket_client_to_value) {
        return $mock_stream_socket_client_to_value;
    }

    return \stream_socket_client($address, $error_code, $error_message);
}

function file_exists(string $str)
{
    global $mock_file_exists_to_false;

    if ($mock_file_exists_to_false) {
        return false;
    }

    return \file_exists($str);
}

function fopen(string $str, string $mode)
{
    global $mock_fopen_to_false;

    if ($mock_fopen_to_false) {
        return false;
    }

    return \fopen($str, $mode);
}

namespace Platine\Framework\Console;
$mock_fopen_to_false = false;
function fopen(string $str, string $mode)
{
    global $mock_fopen_to_false;

    if ($mock_fopen_to_false) {
        return false;
    }

    return \fopen($str, $mode);
}


namespace Platine\Framework\Helper\Timer;
$mock_microtime_to_1 = false;
function microtime(bool $as_float = false)
{
    global $mock_microtime_to_1;

    if ($mock_microtime_to_1) {
        return 1;
    }

    return \microtime($as_float);
}
