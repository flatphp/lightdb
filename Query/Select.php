<?php namespace Lightdb\Query;


use Lightdb\Conn;

class Select extends QueryAbstract
{
    protected $table;
    protected $select = '*';
    protected $found_rows = '';
    /**
     * @var JoinSql[]
     */
    protected $joins = [];
    /**
     * @var WhereSql
     */
    protected $where;
    protected $group = '';
    protected $order = '';
    protected $limit = '';
    protected $offset = '';

    public function __construct(Conn $conn, $table, $select = '*')
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->select = $select;
        $this->where = new WhereSql();
    }

    public function withCount()
    {
        $this->found_rows = ' SQL_CALC_FOUND_ROWS ';
        return $this;
    }

    public function leftJoin($table, $on, $bind = null)
    {
        $this->joins[] = new JoinSql(JoinSql::TYPE_LEFT, $table, $on, $bind);
        return $this;
    }

    public function rightJoin($table, $on, $bind = null)
    {
        $this->joins[] = new JoinSql(JoinSql::TYPE_RIGHT, $table, $on, $bind);
        return $this;
    }

    public function innerJoin($table, $on, $bind = null)
    {
        $this->joins[] = new JoinSql(JoinSql::TYPE_INNER, $table, $on, $bind);
        return $this;
    }

    public function fullJoin($table, $on, $bind = null)
    {
        $this->joins[] = new JoinSql(JoinSql::TYPE_FULL, $table, $on, $bind);
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
        $sql = 'SELECT ' . $this->found_rows . $this->select .' FROM '. $this->table;
        $bind = [];
        // join
        foreach ($this->joins as $join) {
            $sql .= $join->getSql();
            $bind = array_merge($bind, $join->getBind());
        }
        // where
        $where = $this->where->getSql();
        if ($where) {
            $sql .= ' WHERE '. $where;
            $bind = array_merge($bind, $this->where->getBind());
        }
        // group by
        $sql .= $this->group;
        // order by
        $sql .= $this->order;
        // limit
        $sql .= $this->limit;
        // offset
        $sql .= $this->offset;

        return ['sql' => $sql, 'bind' => $bind];
    }

    public function fetchAll()
    {
        $query = $this->assemble();
        return $this->conn->fetchAll($query['sql'], $query['bind']);
    }

    public function fetchAllTo($name)
    {
        $query = $this->assemble();
        return $this->conn->fetchAllTo($name, $query['sql'], $query['bind']);
    }

    public function fetchAllIndexed()
    {
        $query = $this->assemble();
        return $this->conn->fetchAllIndexed($query['sql'], $query['bind']);
    }

    public function fetchAllIndexedTo($name)
    {
        $query = $this->assemble();
        return $this->conn->fetchAllIndexedTo($name, $query['sql'], $query['bind']);
    }

    public function fetchAllGrouped()
    {
        $query = $this->assemble();
        return $this->conn->fetchAllGrouped($query['sql'], $query['bind']);
    }

    public function fetchAllGroupedTo($name)
    {
        $query = $this->assemble();
        return $this->conn->fetchAllGroupedTo($name, $query['sql'], $query['bind']);
    }

    public function fetchRow()
    {
        $query = $this->assemble();
        return $this->conn->fetchRow($query['sql'], $query['bind']);
    }

    public function fetchRowTo($name)
    {
        $query = $this->assemble();
        return $this->conn->fetchRowTo($name, $query['sql'], $query['bind']);
    }

    public function fetchColumn()
    {
        $query = $this->assemble();
        return $this->conn->fetchColumn($query['sql'], $query['bind']);
    }

    public function fetchPairs()
    {
        $query = $this->assemble();
        return $this->conn->fetchPairs($query['sql'], $query['bind']);
    }

    public function fetchPairsGrouped()
    {
        $query = $this->assemble();
        return $this->conn->fetchPairsGrouped($query['sql'], $query['bind']);
    }

    public function fetchOne()
    {
        $query = $this->assemble();
        return $this->conn->fetchOne($query['sql'], $query['bind']);
    }

    public function count()
    {
        $sql = 'SELECT FOUND_ROWS()';
        return $this->conn->fetchOne($sql);
    }
}