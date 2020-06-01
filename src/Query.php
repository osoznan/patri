<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace osoznan\patri;

/**
 * Class Query
 * The Query builder for MySQL
 */
class Query extends TopObject {

    /** @var Model */
    private $modelClass;
    private $selects = [];
    private $wheres = [];
    private $orders = [];
    private $limit;
    private $offset;
    private $joins = [];
    private $groups = [];

    private $isArrayResult = false;

    public function __construct($class) {
        $this->modelClass = $class;
    }

    public function select($data) {
        $this->selects = [];
        $this->addSelect($data);

        return $this;
    }

    public function addSelect($data) {
        $this->selects[] = $data;

        return $this;
    }

    public function orderBy($data) {
        $this->orders = [];
        $this->addOrderBy($data);

        return $this;
    }

    public function addOrderBy($data) {
        $this->orders[] = $data;

        return $this;
    }

    public function groupBy($data) {
        $this->groups = [];
        $this->addGroupBy($data);

        return $this;
    }

    public function addGroupBy($data) {
        $groups[] = $data;

        return $this;
    }

    public function limit($count = null) {
        $this->limit = $count;

        return $this;
    }

    public function offset($value) {
        $this->offset = $value;

        return $this;
    }

    public function where($data) {
        if (!$data) {
            return $this;
        }
        $this->wheres[] = $this->_processWhere($data);

        return $this;
    }

    public function join($type, $table, $on) {
        $this->joins[] = [
            'type' => $type . ' JOIN',
            'table' => $table,
            'on' => $on
        ];

        return $this;
    }

    public function count() {
        $this->remSelects = join(',', $this->selects);
        $this->selects = ['COUNT(*) as count'];

        $res = $this->asArray()->one();

        $this->selects = explode(',', $this->remSelects);

        return (int)$res['count'];
    }

    public function asArray() {
        $this->isArrayResult = true;

        return $this;
    }

    public function all() {
        $query = $this->getSqlCommand();
        $res = Top::$app->db->execute($query);
        $rows = [];

        if ($this->isArrayResult) {
            for ($i = 0; $i < ($res ? $res->num_rows : 0); $i++) {
                $row = mysqli_fetch_assoc($res);
                $rows[$row['id']] = $row;
            }
        } else {
            for ($i = 0; $i < ($res ? $res->num_rows : 0); $i++) {
                $row = mysqli_fetch_assoc($res);
                $rows[$row['id']] = new $this->modelClass($row);
            }
        }

        return $rows;
    }

    public function one() {
        $query = $this->getSqlCommand();

        $res = Top::$app->db->execute($query);

        $row = mysqli_fetch_assoc($res);
        if (!$row) {
            return false;
        } elseif (!$this->isArrayResult) {
            $row = new $this->modelClass($row);
        }

        return $row;
    }

    public function getSqlCommand() {
        $select = join(',', $this->selects);
        if (empty($select)) {
            $select = "*";
        }

        $order = join(',', $this->orders);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        $where = join(' AND ', $this->wheres);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $join = [];
        foreach ($this->joins as $_join) {
            $join[] = $_join['type'] . ' ' . $_join['table'] . ' ON ' . $_join['on'];
        }
        $join = join(' ', $join);

        $group = join(',', $this->groups);
        if ($this->groups) {
            $group = 'GROUP BY ' . $this->groups;
        }

        if ($this->limit) {
            $limit = 'LIMIT ' . $this->limit;
        }

        if ($this->offset) {
            $offset = 'OFFSET ' . $this->offset;
        }

        $limit = $limit ?? null;
        $offset = $offset ?? null;

        return "SELECT $select FROM `" . $this->modelClass::tableName() . "` $join $where $order $group $limit $offset";
    }

    public function getModelClass() {
        return $this->modelClass;
    }

    /**
     * adds if needed: sql quotes, escapes characters
     */
    public static function toSqlFieldValue($v) {
        if ($v === null) {
            return 'NULL';
        }

        if (filter_var($v, FILTER_VALIDATE_FLOAT || FILTER_VALIDATE_INT) !== FALSE) {
            return $v;
        } else {
            // escape if string and add quotes
            return "'" . str_replace("'", "''", $v) . "'";
        }
    }


    public static function _processWhere($clause) {
        if (is_array($clause)) {
            $key = array_keys($clause)[0];
            if (is_array($clause[$key])) {
                foreach ($clause[$key] as &$fldValue) {
                    $fldValue = static::toSqlFieldValue($fldValue);
                }

                return $key . ' IN (' . join(', ', $clause[$key]) . ')';
            } else {
                $operation = $clause[1] ?? '=';
                return $key . $operation . '\'' . $clause[$key] . '\'';
            }
        }

        return $clause;
    }
}
