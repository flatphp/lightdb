<?php namespace Lightdb\Query;


use Lightdb\Conn;

class Delete extends QueryAbstract
{
    protected $table;
    /**
     * @var WhereSql
     */
    protected $where;
    protected $order = '';
    protected $limit = '';

    public function __construct(Conn $conn, $table)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->where = new WhereSql();
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

    public function orderBy($order)
    {
        $this->order = ' ORDER BY ' . $order;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = ' LIMIT ' . $limit;
        return $this;
    }

    protected function assemble()
    {
        $sql = 'DELETE FROM '. $this->table;
        $bind = [];
        // where
        $where = $this->where->getSql();
        if ($where) {
            $sql .= ' WHERE '. $where;
            $bind = array_merge($bind, $this->where->getBind());
        }
        // order by
        $sql .= $this->order;
        // limit
        $sql .= $this->limit;

        return ['sql' => $sql, 'bind' => $bind];
    }

    public function execute()
    {
        $query = $this->assemble();
        return $this->conn->execute($query['sql'], $query['bind']);
    }
}