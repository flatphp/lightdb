<?php namespace Lightdb\Query;


class InsertSql extends SqlAbstract
{
    public function __construct($table, array $data)
    {
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
}