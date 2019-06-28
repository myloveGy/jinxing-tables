<?php

class PdoObject
{
    /**
     * @var null|\PDO
     */
    private static $pdo = null;

    /**
     * @var string 执行最后的sql
     */
    public $lastSql = '';

    /**
     * @var string 查询的字段
     */
    public $select = '*';

    /**
     * @var string 查询的条件
     */
    public $where = '';

    public $table = '';

    public $limit = '';

    public $all = false;

    /**
     * @var array 查询条件
     */
    public $condition = [];

    /**
     * @var array 绑定的参数
     */
    public $bind = [];

    /**
     * @var array 默认配置信息
     */
    private static $options = [
        'dns'      => 'mysql:host=127.0.0.1;port=3306;charset=utf8',
        'username' => 'root',
        'password' => '',
    ];

    /**
     * Pdo constructor. 私有构造方法
     */
    private function __construct()
    {
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * getInstance() 获取 db 信息
     *
     * @param array $options
     *
     * @return PdoObject
     */
    public static function getInstance($options = [])
    {
        if (self::$pdo == null) {
            // 处理配置信息
            if ($options) {
                self::$options = array_merge(self::$options, $options);
            }

            // 实例化对象
            self::$pdo = new \PDO(self::$options['dns'], self::$options['username'], self::$options['password']);
        }

        return new self;
    }

    public function getPdo()
    {
        return self::$pdo;
    }

    /**
     * 执行新增数据
     *
     * @param string $table  新增数据的表
     * @param array  $insert 新增的数组[字段 => 值]
     *
     * @return bool|string
     */
    public function insert($table, array $insert)
    {
        $keys       = array_keys($insert);
        $bindParams = array_pad([], count($keys), '?');
        // 执行的SQL
        $this->lastSql = 'INSERT INTO `' . $table . '` (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $bindParams) . ')';
        $smt           = self::$pdo->prepare($this->lastSql);
        if ($mixReturn = $smt->execute(array_values($insert))) {
            $mixReturn = self::$pdo->lastInsertId();
        }

        return $mixReturn;
    }

    public function update($table, $where, array $update)
    {
        $this->bind = $this->condition = [];
        $this->buildQuery($where);
        $update_bind = [];
        foreach ($update as $key => $value) {
            $index              = ':update_' . $key;
            $this->bind[$index] = $value;
            $update_bind[]      = "`{$key}` = {$index}";
        }

        $this->lastSql = 'UPDATE `' . $table . '` SET ' . implode($update_bind, ', ') . $this->where;
        $smt           = self::$pdo->prepare($this->lastSql);
        if ($mixed = $smt->execute($this->bind)) {
            $mixed = $smt->rowCount();
        }

        return $mixed;
    }

    public function delete($table, $where)
    {
        $this->bind = $this->condition = [];
        $this->buildQuery($where);
        $this->lastSql = 'DELETE FROM `' . $table . '`' . $this->where;
        $smt           = self::$pdo->prepare($this->lastSql);
        if ($mixed = $smt->execute($this->bind)) {
            $mixed = $smt->rowCount();
        }

        return $mixed;
    }

    /**
     * 查询数据全部数据
     *
     * @param string $table  查询的表格
     * @param array  $where  查询条件
     * @param string $fields 查询的字段
     *
     * @return array
     */
    public function findAll($table, $where = [], $fields = '*')
    {
        $this->bind = $this->condition = [];
        $this->select($fields);
        $this->buildQuery($where);
        $this->lastSql = 'SELECT ' . $this->select . ' FROM `' . $table . '` ' . $this->where;
        $smt           = self::$pdo->prepare($this->lastSql);
        $smt->execute($this->bind);
        return $smt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findAllColumn($table, $where = [], $column = [])
    {
        if ($all = $this->findAll($table, $where, $column)) {
            return array_column($all, $column);
        }

        return [];
    }

    /**
     * 查询数据一条数据
     *
     * @param string $table  查询的表格
     * @param array  $where  查询条件
     * @param string $fields 查询的字段
     *
     * @return array
     */
    public function findOne($table, $where = [], $fields = '*')
    {
        $this->bind = $this->condition = [];
        $this->select($fields);
        $this->buildQuery($where);
        $this->lastSql = 'SELECT ' . $this->select . ' FROM `' . $table . '` ' . $this->where . ' LIMIT 1';
        $smt           = self::$pdo->prepare($this->lastSql);
        $smt->execute($this->bind);
        return $smt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     *
     * 查询单个字段信息
     *
     * @param  string $table  查询的表
     * @param  array  $where  查询的条件
     * @param  string $column 查询的字段
     *
     * @return bool|mixed
     */
    public function findColumn($table, $where = [], $column = [])
    {
        if ($one = $this->findOne($table, $where, [$column])) {
            return get_value($one, $column);
        }

        return false;
    }

    public function query()
    {
        $this->buildQuery();
        $this->lastSql = 'SELECT ' . $this->select . ' FROM ' . $this->table . ' ' . $this->where . ' ' . $this->limit;
        $smt           = self::$pdo->prepare($this->lastSql);
        $smt->execute($this->bind);
        return $this->all ? $smt->fetchAll(\PDO::FETCH_ASSOC) : $smt->fetch(\PDO::FETCH_ASSOC);
    }

    public function one()
    {
        $this->all   = false;
        $this->limit = 'LIMIT 1';
        return $this->query();
    }

    public function all()
    {
        $this->all = true;
        return $this->query();
    }

    /**
     * 查询的字段
     *
     * @param string|array $field 查找的字段
     *
     * @return $this
     */
    public function select($field = '*')
    {
        $field = is_string($field) ? explode(',', $field) : (array)$field;
        foreach ($field as &$value) {
            if ($value !== '*') {
                $value = '`' . str_replace([' as ', ' AS '], '` as `', trim($value)) . '`';
            }
        }

        unset($value);
        $this->select = implode(', ', $field);
        return $this;
    }

    /**
     * 查询表
     *
     * @param $table
     *
     * @return $this
     */
    public function from($table)
    {
        $this->table = '`' . $table . '`';
        return $this;
    }

    /**
     * 查询条件
     *
     * @param array $where
     *
     * @param array $bind
     *
     * @return $this
     */
    public function where($where = [], $bind = [])
    {
        // 没有传递查询条件
        if (empty($where)) {
            return $this;
        }

        // 传递的字符串
        if (is_string($where)) {
            $this->condition[] = $where;
            foreach ($bind as $column => $bind_value) {
                $this->bind[$column] = $bind_value;
            }

            return $this;
        }

        // 数组处理
        if (isset($where[0])) {
            $and      = is_string($where[0]) ? strtoupper(trim(array_shift($where))) : 'AND';
            $arrWhere = [];
            foreach ($where as $value) {
                // 'column' => 'value1' or 'column' => ['value1', 'value2', 'value3']
                if (count($value) == 1) {
                    $arrWhere[] = $this->buildHasCondition($value);
                } else {
                    list($column, $operate, $bind_value) = $value;
                    $arrWhere[] = $this->buildOperateCondition($operate, $column, $bind_value);
                }
            }

            $this->condition[] = count($arrWhere) === 1 ? $arrWhere[0] : '(' . implode(') ' . $and . ' (', $arrWhere) . ')';
            return $this;
        }

        // hash format: 'column1' => 'value1', 'column2' => 'value'
        $this->condition[] = $this->buildHasCondition($where);
        return $this;

    }

    public function buildInCondition($column, $value)
    {
        $array_column = [];
        foreach ($value as $key => $bind_value) {
            $bind_column              = ':' . $column . '_' . $key;
            $array_column[]           = $bind_column;
            $this->bind[$bind_column] = $bind_value;
        }

        return '`' . $column . '` IN (' . implode(', ', $array_column) . ')';
    }

    public function buildHasCondition($condition)
    {
        $parts = [];
        foreach ($condition as $column => $value) {
            $parts[] = $this->buildOperateCondition('=', $column, $value);
        }

        return count($parts) === 1 ? $parts[0] : '(' . implode(') AND (', $parts) . ')';
    }

    public function buildOperateCondition($operate, $column, $value)
    {
        if (is_array($value) || in_array($operate, ['in', 'IN'])) {
            return $this->buildInCondition($column, $value);
        }

        if ($value === null) {
            return $parts[] = "`{$column}` IS NULL";
        }

        $this->bind[':' . $column] = $value;
        return "`{$column}` {$operate} :{$column}";
    }

    public function buildQuery($where = [])
    {
        if ($where) {
            $this->where($where);
        }

        if (empty($this->condition)) {
            $this->where = '';
            $this->bind  = [];
        } else {
            $this->where = ' WHERE ' . implode(' AND ', $this->condition);
        }
    }

    /**
     * 查询数据条数
     *
     * @param     $length
     * @param int $start
     *
     * @return $this
     */
    public function limit($length, $start = 0)
    {
        $this->limit = 'LIMIT ' . intval($start) . ', ' . intval($length);
        return $this;
    }

    public function getLastSql()
    {
        $sql = $this->lastSql;
        foreach ($this->bind as $column => $bind_value) {
            $replace = is_numeric($bind_value) ? $bind_value : "'{$bind_value}'";
            $sql     = preg_replace('/' . $column . '/', $replace, $sql, 1);
        }

        return $sql;
    }
}