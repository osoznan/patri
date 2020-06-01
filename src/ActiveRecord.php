<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace osoznan\patri;

/**
 * Class ActiveRecord
 * The base ActiveRecord object
 *
 */
class ActiveRecord extends Model {
    const EVENT_BEFORE_INSERT = 'before-insert';
    const EVENT_AFTER_INSERT = 'after-insert';
    const EVENT_BEFORE_UPDATE = 'before-update';
    const EVENT_AFTER_UPDATE = 'after-update';

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

        $res = Top::$app->db->execute("INSERT INTO `". static::tableName() . "` ($fields) VALUES($values)");

        return $res !== false ? mysqli_insert_id(Top::$app->db->connect()) : false;
    }

    public static function update(array $values, $where) {
        $where = $where ? ' WHERE ' . Query::_processWhere($where) : null;

        $fields = [];
        foreach ($values as $key => $value) {
            $fields[] = "$key = " . Query::toSqlFieldValue($value);
        }
        $fieldsStr = join(', ', $fields);

        return Top::$app->db->execute("UPDATE `". static::tableName() . "` SET $fieldsStr " . $where) !== false;
    }

    public static function delete($where = null) {
        $where = $where ? (' WHERE ' . Query::_processWhere($where)) : null;

        return Top::$app->db->execute("DELETE FROM `". static::tableName() . "` $where");
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
            $elem = self::find()->where(['id' => $id])->one();

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
