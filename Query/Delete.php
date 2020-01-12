<?php namespace Lightdb\Query;


use Lightdb\Conn;

class Delete extends QueryAbstract
{
    protected $table;
    /**
     * @var Where
     */
    protected $where;
    protected $order = '';
    protected $limit = '';

    public function __construct(Conn $conn, $table)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->where = new Where();
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
        if ($this->sql) {
            return;
        }
        $this->sql = 'DELETE FROM '. $this->table;
        // where
        $where = $this->where->getSql();
        if ($where) {
            $this->sql .= ' WHERE '. $where;
            $this->bind = array_merge($this->bind, $this->where->getBind());
        }
        // order by
        $this->sql .= $this->order;
        // limit
        $this->sql .= $this->limit;
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

    public function execute()
    {
        return $this->conn->execute($this->getSql(), $this->bind);
    }
}