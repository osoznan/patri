<?php

namespace app\system;

class ModelValidator {

    public static function required($value) {
        if (!isset($value) || strlen($value) == 0) {
            return 'Не указано значение';
        }

        return true;
    }

    public static function str($value, $mode) {
        $len = reset($mode);

        if (key($mode) == 'min' && strlen($value) < $len) {
            return 'Длина не может быть меньше ' . $len . ' символов';
        }

        if (key($mode) == 'max' && strlen($value) > $len) {
            return 'Длина не может быть больше ' . $len . ' символов';
        }

        return true;
    }

    public static function email($value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Неверный e-mail адрес';
        }

        return true;
    }

}
