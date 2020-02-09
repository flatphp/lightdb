<?php namespace Lightdb\Query;


use Lightdb\Conn;

class Insert extends QueryAbstract
{
    /**
     * @var InsertSql
     */
    protected $insert;

    public function __construct(Conn $conn, $table, array $data)
    {
        $this->conn = $conn;
        $this->insert = new InsertSql($table, $data);
    }

    protected function assemble()
    {
        return ['sql' => $this->insert->getSql(), 'bind' => $this->insert->getBind()];
    }

    public function execute()
    {
        $query = $this->assemble();
        return $this->conn->execute($query['sql'], $query['bind']);
    }
}