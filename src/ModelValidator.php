<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace osoznan\patri;

/**
 * Class ModelValidator
 * Built in model validators
 */
class ModelValidator extends TopObject {

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

    public static function int($value, $mode) {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return 'Неверно введено целое число';
        }

        $len = reset($mode);

        if (key($mode) == 'min' && $value < $len) {
            return 'Не может быть меньше ' . $len;
        }

        if (key($mode) == 'max' && $value > $len) {
            return 'Не может быть больше ' . $len;
        }

        return true;
    }

    public static function email($value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Неверный e-mail адрес';
        }

        return true;
    }

    public static function regexp($value, $regexp) {
        if (preg_match($regexp[2], $value) === 0) {
            return 'Неверный ввод';
        }

        return true;
    }

}
