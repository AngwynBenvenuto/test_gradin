<?php

if (!function_exists('set_active')) {
    function set_active($uri, $output = 'active open') {
        if( is_array($uri) ) {
            foreach ($uri as $u) {
                if (\Route::is($u)) {
                    return $output;
                }
            }
        } else {
            if (\Route::is($uri)){
                return $output;
            }
        }
    }
}
if (!function_exists('load_css')) {
    function load_css(array $array) {
        foreach ($array as $uri) {
            echo "<link rel='stylesheet' type='text/css' href='" . $uri . "?v=".uniqid()."' />";
        }
    }
}
if (!function_exists('load_js')) {
    function load_js(array $array) {
        foreach ($array as $uri) {
            echo "<script type='text/javascript'  src='" . $uri . "?v=".uniqid()."'></script>";
        }
    }
}
if (!function_exists('get_actual_controller_name')) {
    function get_actual_controller_name($router) {
        $pices = explode('\\', $router);
        $pices1 = explode('@', end($pices));
        $controller_name = current($pices1);
        return ucfirst(str_replace('Controller', '', $controller_name));
    }
}

if (!function_exists('get_real_ip')) {

    function get_real_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
/**
 * get the defined config value by a key
 * @param string $key
 * @return config value
 */
if (!function_exists('get_setting')) {
    function get_setting($key = "") {
        // $setting_value = get_array_value(config('Rise')->app_settings_array, $key);
        // if ($setting_value !== NULL) {
        //     return $setting_value;
        // } else {
        //     if (isset(config('Rise')->$key)) {
        //         return config('Rise')->$key;
        //     } else if (isset(config('App')->$key)) {
        //         return config('App')->$key;
        //     } else {
        //         return "";
        //     }
        // }
    }
}
if (!function_exists('is_localhost')) {
    function is_localhost() {
        $known_localhost_ip = array(
            '127.0.0.1',
            '::1'
        );
        if (in_array(get_real_ip(), $known_localhost_ip)) {
            return true;
        }
    }
}