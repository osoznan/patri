<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

use app\models\Element;

/**
 * Class Model
 * The base Model object
 *
 */
class Model extends Component {
    const EVENT_BEFORE_INSERT = 'before-insert';
    const EVENT_AFTER_INSERT = 'after-insert';
    const EVENT_BEFORE_UPDATE = 'before-update';
    const EVENT_AFTER_UPDATE = 'after-update';

    protected $rows;

    protected $errors;

    public function __construct(array $attributes = null) {
        $attributes ? $this->load($attributes) : null;

        $this->init();
    }

    public function init() {}

    public static function tableName() {
        return null;
    }

    public function rules() {
        return [];
    }

    public static function labels() {
        return [];
    }

    public function validateRule($rule, $attribute) {
        $validator = next($rule);
        $param = next($rule) ? [key($rule) => current($rule)] : null;

        if (!is_callable($validator)) {
            $result = ModelValidator::$validator($this->$attribute ?? null, $param);
            if ($result !== true) {
                $this->errors[$attribute][] = $result;
            }
        } else {
            call_user_func($validator, $this);
        }
    }

    /**
     * Validation process of a single record data, must be overriden in a child model to perform a check
     *
     * @return bool
     */
    public function validate($attributes = null) {
        $this->errors = [];

        foreach ($this->rules() as $rule) {
            $attribute = reset($rule);

            foreach (is_array($attribute) ? $attribute : [ $attribute ] as $attr) {
                if (!$attributes || in_array($attr, $attributes)) {
                    $this->validateRule($rule, $attr);
                }
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
                if ($this->$value !== null) {
                    $attrs[$value] = $this->$value;
                }
            }
        }

        return $attrs;
    }

    public function load(array $data) {
        foreach (array_merge($this->attributes(), ['id' => $data['id'] ?? null]) as $attr) {
            if (array_key_exists($attr, $data)) {
                $this->$attr = $data[$attr];
            }
        }

        $this->id = $data['id'] ?? null;

        return true;
    }

    public function getErrors($attr = null) {
        if ($attr) {
            return $this->errors[$attr];
        }

        return $this->errors;
    }

    public function getFirstError($attr = null) {
        if ($attr) {
            return $this->getErrors($attr)[0];
        }

        $errors = $this->getErrors();
        return reset($errors);
    }

    public function getFirstErrors() {
        $errors = [];
        foreach ($this->getErrors() as $key => $errorContent) {
            $errors[$key] = is_array($errorContent) ? $first = reset($errorContent) : $errorContent;
        }

        return $errors;
    }

    public static function getLabel($name) {
        return isset(static::labels()[$name]) ? ucfirst(static::labels()[$name]) : $name;
    }

    public static function find() {
        return new Query(static::class);
    }

    public function save() {
        $data = $this->getAttributes();

        if (isset($this->id)) {
            $this->trigger(static::EVENT_BEFORE_UPDATE);

            if ($this::update($data, ['id' => $this->id])) {
                $this->trigger(static::EVENT_AFTER_UPDATE);
            }
        } else {
            $this->trigger(static::EVENT_BEFORE_INSERT);

            $result = $this::insert($data);
            if ($result) {
                $this->id = $result;

                $this->trigger(static::EVENT_AFTER_INSERT);
            }
        }

        return $result;
    }

    public static function insert(array $data) {
        list($fields, $values) = [[], []];

        foreach ($data as $key => $value) {
            $fields[] = "`" . $key . "`";
            $values[] = Query::toSqlFieldValue($value);
        }

        $fields = join(',', $fields);
        $values = join(',', $values);

        $res = Db::execute("INSERT INTO `". static::tableName() . "` ($fields) VALUES($values)");

        return $res !== false ? mysqli_insert_id(Db::connect()) : false;
    }

    public static function update(array $values, $where) {
        $where = $where ? ' WHERE ' . Query::_processWhere($where) : null;

        $fields = [];
        foreach ($values as $key => $value) {
            $fields[] = "$key = " . Query::toSqlFieldValue($value);
        }
        $fieldsStr = join(', ', $fields);

        return Db::execute("UPDATE `". static::tableName() . "` SET $fieldsStr " . $where) !== false;
    }

    public static function delete($where = null) {
        $where = $where ? (' WHERE ' . Query::_processWhere($where)) : null;

        return Db::execute("DELETE FROM `". static::tableName() . "` $where");
    }

    public static function count() {
        $result = mysqli_query(Db::connect(), "SELECT COUNT(*) FROM `". static::tableName() . "`");
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

    public static function setColumnValue($column, $ids, $value) {
        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $id) {
            if (!static::update([$column => $value], ['id' => $id])) {
                return 'error';
            }
        }

        return true;
    }

    public static function setColumnValueValidated($column, $ids, $value) {
        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $id) {
            $elem = Element::find()->where(['id' => $id])->one();

            // set the column which is to change
            $elem->$column = $value;

            if ($elem->validate([$column])) {
                static::update([$column => $value], ['id' => $id]);
            } else {
                return $elem->getFIrstErrors();
            }
        }

        return true;
    }
}
