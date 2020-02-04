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

    public function __construct(array $attributes = null) {
        $attributes ? $this->load($attributes) : null;
    }

    public static function tableName() {
        return null;
    }

    public function rules() {
        return [];
    }

    public static function labels() {
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

    public function getAttributes() {
        $attrs = [];
        foreach (array_merge($this->attributes(), ['id']) as $value) {
            if (isset($this->$value)) {
                $attrs[$value] = $this->$value;
            } else {
                $attrs[$value] = null;
            }
        }

        return $attrs;
    }

    public function load(array $data) {
        foreach (array_merge($this->attributes(), ['id' => $data['id'] ?? null]) as $value) {
            if (isset($data[$value])) {
                $this->$value = $data[$value];
            } else {
                $this->$value = null;
            }
        }

        $this->id = $data['id'] ?? null;

        return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    public static function getLabel($name) {
        return isset(static::labels()[$name]) ? ucfirst(static::labels()[$name]) : $name;
    }

    public static function find() {
        return new Query(static::class);
    }

    public function save() {
        $data = [];
        foreach ($this->attributes() as $attr) {
            $data[$attr] = $this->$attr;
        }

        if ($this->id) {
            $this->update($data, ['id' => $this->id]);
        } else {
            $result = $this->insert($data);
            if ($result) {
                $this->id = $result;
            }
        }
    }

    public function insert(array $data) {
        list($fields, $values) = [[], []];

        foreach ($data as $key => $value) {
            $fields[] = "`" . $key . "`";
            $values[] = (isset($value) ? "'$value'" : 'NULL');;
        }

        $fields = join(',', $fields);
        $values = join(',', $values);

        $res = Db::execute("INSERT INTO `". $this->tableName() . "` ($fields) VALUES($values)");

        return $res !== false ? mysqli_insert_id(Db::connect()) : false;
    }

    public function update(array $values, $where) {
        $where = $where ? ' WHERE ' . Query::_processWhere($where) : null;

        $fields = [];
        foreach ($values as $key => $value) {
            $fields[] = "$key = " . (isset($value) ? "'$value'" : 'NULL');
        }
        $fieldsStr = join(', ', $fields);



        return Db::execute("UPDATE `". $this->tableName() . "` SET $fieldsStr " . $where) !== false;
    }

    public function delete($where) {
        $where = $where ? ' WHERE ' . Query::_processWhere($where) : null;

        return Db::execute("DELETE FROM `". $this->tableName() . "` $where");
    }

    public function count() {
        $result = mysqli_query(Db::connect(), "SELECT COUNT(*) FROM `". $this->tableName() . "`");
        return mysqli_fetch_row($result)[0];
    }

    public static function addFieldLabelsToErrorMessages($errors) {
        if (!empty($errors)) {
            foreach ($errors as $attr => &$error) {
                $error = (static::labels()[$attr] ?? $attr) . ': ' . $error;
            }
        }

        return $errors;
    }
}
