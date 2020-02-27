<?php namespace Lightdb;


class Query
{
    /**
     * @var Conn
     */
    protected $conn;
    protected $table;
    protected $select = '*';
    protected $joins = [];
    protected $wheres = [];
    protected $group = '';
    protected $order = '';
    protected $limit = '';
    protected $offset = '';
    protected $sql = '';
    protected $bind = [];
    protected $join_binds = [];
    protected $where_binds = [];

    public function __construct(Conn $conn, array $options = [])
    {
        $this->conn = $conn;
        if (isset($options['master']) && $options['master'] == true) {
            $this->conn->master();
        }
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($select)
    {
        $this->select = $select;
        return $this;
    }

    protected function join($type, $table, $on, $bind = null)
    {
        $this->joins[] = array(
            'type' => $type,
            'table' => $table,
            'on' => $on
        );
        $this->join_binds = $this->addBind($this->join_binds, $bind);
        return $this;
    }

    public function leftJoin($table, $on, $bind = null)
    {
        return $this->join('LEFT', $table, $on, $bind);
    }

    public function rightJoin($table, $on, $bind = null)
    {
        return $this->join('RIGHT', $table, $on, $bind);
    }

    public function innerJoin($table, $on, $bind = null)
    {
        return $this->join('INNER', $table, $on, $bind);
    }

    public function fullJoin($table, $on, $bind = null)
    {
        return $this->join('FULL', $table, $on, $bind);
    }

    public function where($where, $bind = null, $type = 'AND')
    {
        $this->wheres[] = array(
            'type' => $type,
            'where' => $where
        );
        $this->where_binds = $this->addBind($this->where_binds, $bind);
        return $this;
    }

    public function whereIn($field, array $values, $type = 'AND', $in = 'IN')
    {
        $this->wheres[] = array(
            'type' => $type,
            'where' => $field .' '. $in .' ('. implode(',', array_fill(0, count($values), '?')) .')'
        );
        $this->where_binds = $this->addBind($this->where_binds, $values);
        return $this;
    }

    public function whereNotIn($field, array $values)
    {
        return $this->whereIn($field, $values, 'AND', 'NOT IN');
    }

    public function orWhere($where, $bind = null)
    {
        return $this->where($where, $bind, 'OR');
    }

    public function orWhereIn($field, array $values)
    {
        return $this->whereIn($field, $values, 'OR', 'IN');
    }

    public function orWhereNotIn($field, array $values)
    {
        return $this->whereIn($field, $values, 'OR', 'NOT IN');
    }

    public function groupBy($group)
    {
        $this->group = ' GROUP BY '. $group;
        return $this;
    }

    public function orderBy($order)
    {
        $this->order = ' ORDER BY '. $order;
        return $this;
    }

    public function limit($limit, $offset = 0)
    {
        $this->limit = ' LIMIT ' . $limit;
        $this->offset($offset);
        return $this;
    }

    public function offset($offset)
    {
        if ($offset > 0) {
            $this->offset = ' OFFSET '. $offset;
        }
        return $this;
    }

    public function page($page, $page_size = 10)
    {
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $page_size;
        $this->limit($page_size, $offset);
        return $this;
    }

    public function fetchAll()
    {
        return $this->conn->fetchAll($this->compileSelect(), $this->bind);
    }

    public function fetchAllIndexed()
    {
        return $this->conn->fetchAllIndexed($this->compileSelect(), $this->bind);
    }

    public function fetchAllGrouped()
    {
        return $this->conn->fetchAllGrouped($this->compileSelect(), $this->bind);
    }

    public function fetchRow()
    {
        return $this->conn->fetchRow($this->compileSelect(), $this->bind);
    }

    public function fetchColumn()
    {
        return $this->conn->fetchColumn($this->compileSelect(), $this->bind);
    }

    public function fetchPairs()
    {
        return $this->conn->fetchPairs($this->compileSelect(), $this->bind);
    }

    public function fetchPairsGrouped()
    {
        return $this->conn->fetchPairsGrouped($this->compileSelect(), $this->bind);
    }

    public function fetchOne()
    {
        return $this->conn->fetchOne($this->compileSelect(), $this->bind);
    }

    /**
     * get total count
     * @return int
     */
    public function count()
    {
        $this->sql = "SELECT COUNT(*) FROM $this->table";
        // join
        foreach ($this->joins as $join) {
            $this->sql .= ' ' . $join['type'] .' JOIN '. $join['table'] .' ON '. $join['on'];
        }
        // where
        $this->sql .= $this->getWhere();
        // group by
        $this->sql .= $this->group;
        $this->bind = array_merge($this->join_binds, $this->where_binds);
        return $this->conn->fetchOne($this->sql, $this->bind);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function insert(array $data)
    {
        $cols = [];
        $vals = [];
        foreach ($data as $k => $v) {
            $cols[] = $k;
            if ($v instanceof Raw) {
                $vals[] = $v->getValue();
                unset($data[$k]);
            } else {
                $vals[] = '?';
            }
        }
        $this->sql = 'INSERT INTO '. $this->table .' ('. implode(', ', $cols) .') VALUES ('. implode(', ', $vals) . ')';
        $this->bind = array_values($data);
        return $this->conn->execute($this->sql, $this->bind);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data)
    {
        $sets = [];
        foreach ($data as $k => $v) {
            if ($v instanceof Raw) {
                $val = $v->getValue();
                unset($data[$k]);
            } else {
                $val = '?';
            }
            $sets[] = $k . '=' . $val;
        }
        $this->sql = 'UPDATE '. $this->table .' SET ' . implode(',', $sets);
        // where
        $this->sql .= $this->getWhere();
        // order by
        $this->sql .= $this->order;
        // limit
        $this->sql .= $this->limit;
        $this->bind = array_merge(array_values($data), $this->where_binds);
        return $this->conn->execute($this->sql, $this->bind);
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $this->sql = 'DELETE FROM '. $this->table;
        // where
        $this->sql .= $this->getWhere();
        // order by
        $this->sql .= $this->order;
        // limit
        $this->sql .= $this->limit;
        $this->bind = $this->where_binds;
        return $this->conn->execute($this->sql, $this->bind);
    }

    /**
     * get queried sql and bind
     * @return array
     */
    public function getLog()
    {
        return array(
            'sql' => $this->sql,
            'bind' => $this->bind
        );
    }

    /**
     * get select sql
     * @return string
     */
    protected function compileSelect()
    {
        $this->sql = "SELECT $this->select FROM $this->table";
        // join
        foreach ($this->joins as $join) {
            $this->sql .= ' ' . $join['type'] .' JOIN '. $join['table'] .' ON '. $join['on'];
        }
        // where
        $this->sql .= $this->getWhere();
        // group by
        $this->sql .= $this->group;
        // order by
        $this->sql .= $this->order;
        // limit
        $this->sql .= $this->limit;
        // offset
        $this->sql .= $this->offset;
        $this->bind = array_merge($this->join_binds, $this->where_binds);
        return $this->sql;
    }

    /**
     * get the part of where sql
     * @return string
     */
    protected function getWhere()
    {
        if (empty($this->wheres)) {
            return '';
        }
        $sql = ' WHERE '. $this->wheres[0]['where'];
        foreach ($this->wheres as $i => $where) {
            if ($i == 0) {
                continue;
            }
            $sql .= ' '. $where['type'] .' '. $where['where'];
        }
        return $sql;
    }

    protected function addBind(array $to, $bind)
    {
        if ($bind !== null) {
            if (is_array($bind)) {
                $to = array_merge($to, array_values($bind));
            } else {
                $to[] = $bind;
            }
        }
        return $to;
    }
}