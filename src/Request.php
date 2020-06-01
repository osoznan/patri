<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace osoznan\patri;

class Request {

    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public static function get($key = null) {
        if (!$key) {
            return $_GET;
        }

        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return null;
    }

    public static function post($key = null) {
        if (!$key) {
            return $_POST;
        }

        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        return null;
    }

}
