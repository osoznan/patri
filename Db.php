<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

class Db {

    public static $connect;

    public static function connect() {
        if (!static::$connect) {
            $db = App::getConfig('db');
            static::$connect = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
            if (!static::$connect) {
                throw new \ErrorException('Ошибка подключения к базе');
            }
        }

        return static::$connect;
    }

    public static function execute($query) {
        $res = mysqli_query(static::connect(), $query);

        if ($res == false) {
            throw new \ErrorException('Ошибка в запросе: ' . $query);
        }

        return $res;
    }

}
