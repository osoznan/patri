<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace osoznan\patri;

/**
 * Class Db
 * Db component
 */
class Db extends Component {
    const EVENT_AFTER_SQL = 'after-sql';

    public $host;
    public $user;
    public $password;
    public $database;

    private $connect;

    public function connect() {
        if (!$this->connect) {
            $this->connect = mysqli_connect($this->host, $this->user, $this->password, $this->database);
            mysqli_set_charset($this->connect,'utf8');

            if (!$this->connect) {
                throw new \ErrorException('Database connection error');
            }
        }

        return $this->connect;
    }

    public function execute($query) {
        $res = mysqli_query($this->connect(), $query);

        $ev = new Event();
        $ev->data = ['sql' => $query, 'success' => $res];
        $this->trigger(self::EVENT_AFTER_SQL, $ev);

        if ($res == false) {
            throw new \ErrorException('Ошибка выполнения запроса: ' . $query);
        }

        return $res;
    }

}
