<?php namespace Lightdb;


class Query
{
    /**
     * @var Conn
     */
    protected $conn;

    public function __construct(Conn $conn, array $options = [])
    {
        $this->conn = $conn;
        if (isset($options['master']) && $options['master'] == true) {
            $this->conn->master();
        }
    }

    public function select($table, $select = '*')
    {
        return new Query\Select($this->conn, $table, $select);
    }

    public function insert($table, $data)
    {
        return new Query\Insert($this->conn, $table, $data);
    }

    public function update($table, $data)
    {
        return new Query\Update($this->conn, $table, $data);
    }

    public function delete($table)
    {
        return new Query\Delete($this->conn, $table);
    }
}