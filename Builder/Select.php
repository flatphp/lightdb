<?php namespace Lightdb\Builder;


use Lightdb\Conn;

class Select extends BuilderAbstract
{
    public $table;
    public $select = '*';
    /**
     * @var Join[]
     */
    public $joins = [];
    /**
     * @var Where
     */
    protected $where;
    public $group = '';
    public $order = '';
    public $limit = '';
    public $offset = '';

    public function __construct(Conn $conn, $table, $select = '*')
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->select = $select;
        $this->where = new Where();
    }

    public function leftJoin($table, $on, $bind = null)
    {
        $this->joins[] = new Join(Join::TYPE_LEFT, $table, $on, $bind);
        return $this;
    }

    public function rightJoin($table, $on, $bind = null)
    {
        $this->joins[] = new Join(Join::TYPE_RIGHT, $table, $on, $bind);
        return $this;
    }

    public function innerJoin($table, $on, $bind = null)
    {
        $this->joins[] = new Join(Join::TYPE_INNER, $table, $on, $bind);
        return $this;
    }

    public function fullJoin($table, $on, $bind = null)
    {
        $this->joins[] = new Join(Join::TYPE_FULL, $table, $on, $bind);
        return $this;
    }

    public function groupBy($group)
    {
        $this->group = ' GROUP BY ' . $group;
        return $this;
    }

    public function orderBy($order)
    {
        $this->order = ' ORDER BY ' . $order;
        return $this;
    }

    public function where($where, $bind = null)
    {
        $this->where = $this->where->where($where, $bind);
        return $this;
    }

    public function orWhere($where, $bind = null)
    {
        $this->where = $this->where->orWhere($where, $bind);
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

    protected function assemble()
    {
        if ($this->sql) {
            return;
        }
        $this->sql = 'SELECT '. $this->select .' FROM '. $this->table;
        // join
        foreach ($this->joins as $join) {
            $this->sql .= $join->getSql();
            $this->bind = array_merge($this->bind, $join->getBind());
        }
        // where
        $where = $this->where->getSql();
        if ($where) {
            $this->sql .= ' WHERE '. $where;
            $this->bind = array_merge($this->bind, $this->where->getBind());
        }
        // group by
        $this->sql .= $this->group;
        // order by
        $this->sql .= $this->order;
        // limit
        $this->sql .= $this->limit;
        // offset
        $this->sql .= $this->offset;
    }

    public function getSql()
    {
        $this->assemble();
        return $this->sql;
    }

    public function getBind()
    {
        $this->assemble();
        return $this->bind;
    }

    public function fetchAll()
    {
        return $this->conn->fetchAll($this->getSql(), $this->bind);
    }

    public function fetchAllIndexed()
    {
        return $this->conn->fetchAllIndexed($this->getSql(), $this->bind);
    }

    public function fetchAllGrouped()
    {
        return $this->conn->fetchAllGrouped($this->getSql(), $this->bind);
    }

    public function fetchRow()
    {
        return $this->conn->fetchRow($this->getSql(), $this->bind);
    }

    public function fetchColumn()
    {
        return $this->conn->fetchColumn($this->getSql(), $this->bind);
    }

    public function fetchPairs()
    {
        return $this->conn->fetchPairs($this->getSql(), $this->bind);
    }

    public function fetchPairsGrouped()
    {
        return $this->conn->fetchPairsGrouped($this->getSql(), $this->bind);
    }

    public function fetchOne()
    {
        return $this->conn->fetchOne($this->getSql(), $this->bind);
    }
}