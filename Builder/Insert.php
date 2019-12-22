<?php namespace Lightdb\Builder;


use Lightdb\Conn;

class Insert extends BuilderAbstract
{
    public function __construct(Conn $conn, $table, array $data)
    {
        $this->conn = $conn;
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
        $this->bind = $this->bind($data);
        $this->sql = 'INSERT INTO '. $table .' ('. implode(', ', $cols) .') VALUES ('. implode(', ', $vals) . ')';
    }

    public function execute()
    {
        return $this->conn->execute($this->sql, $this->bind);
    }
}