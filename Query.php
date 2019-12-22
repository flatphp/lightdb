<?php namespace Lightdb;

use Lightdb\Builder;

class Query
{
    /**
     * @var Conn
     */
    protected $conn;

    public function __construct(Conn $conn)
    {
        $this->conn = $conn;
    }

    public function select($table, $select = '*')
    {
        return new Builder\Select($this->conn, $table, $select);
    }

    public function insert($table, $data)
    {
        return new Builder\Insert($this->conn, $table, $data);
    }

    public function update($table, $data)
    {
        return new Builder\Update($this->conn, $table, $data);
    }

    public function delete($table)
    {
        return new Builder\Delete($this->conn, $table);
    }
}