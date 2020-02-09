<?php namespace Lightdb\Query;


use Lightdb\Conn;

class Update extends QueryAbstract
{
    /**
     * @var UpdateSql
     */
    protected $update;
    /**
     * @var WhereSql
     */
    protected $where;
    protected $order = '';
    protected $limit = '';

    public function __construct(Conn $conn, $table, array $data)
    {
        $this->conn = $conn;
        $this->update = new UpdateSql($table, $data);
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
        $sql = $this->update->getSql();
        $bind = $this->update->getBind();

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