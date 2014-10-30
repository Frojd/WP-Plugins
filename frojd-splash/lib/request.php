<?php

namespace Frojd\Plugins\Splash\Lib;

/**
 * Helper for retriving post values.
 */
class Request {
    public static function post($field, $default = '') {
        return isset($_POST[$field]) ? $_POST[$field] : $default;
    }
}
