<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

/**
 * Class Model
 * The base Model object
 *
 */
class Model extends TopObject {
    protected $rows;

    protected $errors;
    protected static $labels;

    public function __construct(array $attributes = null) {
        $attributes ? $this->load($attributes) : null;
    }

    public static function tableName() {
        return null;
    }

    public function rules() {
        return [];
    }

    /**
     * Validation process of a single record data, must be overriden in a child model to perform a check
     *
     * @return bool
     */
    public function validate() {
        $this->errors = [];

        foreach ($this->rules() as $rule) {
            $attribute = reset($rule);
            $validator = next($rule);

            $param = next($rule) ? [key($rule) => current($rule)] : null;

            if (!is_callable($validator)) {
                $result = ModelValidator::$validator($this->$attribute ?? null, $param);
                if ($result !== true) {
                    $this->errors[$attribute] = $result;
                }
            } else {
                call_user_func($validator, $this);
            }
        }

        return count($this->errors) == 0;
    }

    public static function attributes() {
        return [];
    }

    public function load(array $data) {
        foreach ($this->attributes() as $value) {
            if (isset($data[$value])) {
                $this->$value = $data[$value];
            } else {
                $this->$value = null;
            }
        }

        return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    public static function getLabel($name) {
        return static::$labels[$name] ?? null;
    }

    public static function find() {
        return new Query(static::class);
    }

    public function insert(array $data) {
        list($fields, $values) = [[], []];

        foreach ($data as $key => $value) {
            $fields[] = "`" . $key . "`";
            $values[] = "'" . $value . "'";
        }

        $fields = join(',', $fields);
        $values = join(',', $values);

        $res = Db::execute("INSERT INTO `". $this->tableName() . "` ($fields) VALUES($values)");

        return $res !== false ? mysqli_insert_id(Db::connect()) : false;
    }

    public function update(array $values, $where) {
        $fields = [];
        foreach ($values as $key => $value) {
            $fields[] = "$key = '$value'";
        }
        $fieldsStr = join(', ', $fields);

        return Db::execute("UPDATE `". $this->tableName() . "` SET $fieldsStr " . ($where ? " WHERE $where" : null)) !== false;
    }

    public function delete($where) {
        $where = $where ? ' WHERE ' . Query::_processWhere($where) : null;

        return Db::execute("DELETE FROM `". $this->tableName() . "` $where");
    }

    public function count() {
        $result = mysqli_query(Db::connect(), "SELECT COUNT(*) FROM `". $this->tableName() . "`");
        return mysqli_fetch_row($result)[0];
    }
}
