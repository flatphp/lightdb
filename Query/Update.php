<?php namespace Lightdb\Query;


use Lightdb\Conn;

class Update extends QueryAbstract
{
    protected $table;
    protected $data;
    /**
     * @var Where
     */
    protected $where;
    protected $order = '';
    protected $limit = '';

    public function __construct(Conn $conn, $table, array $data)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->data = $data;
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
        $sets = [];
        foreach ($this->data as $k => $v) {
            if ($v instanceof Raw) {
                $val = $v->getValue();
                unset($this->data[$k]);
            } else {
                $val = '?';
            }
            $sets[] = $k . '=' . $val;
        }
        $this->sql = 'UPDATE '. $this->table .' SET ' . implode(',', $sets);
        $this->bind = $this->bind($this->data);
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