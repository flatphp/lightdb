<?php namespace Lightdb\Query;

class UpdateSql extends SqlAbstract
{
    public function __construct($table, $data)
    {
        $sets = [];
        foreach ($data as $k => $v) {
            if ($v instanceof Raw) {
                $val = $v->getValue();
                unset($data[$k]);
            } else {
                $val = '?';
            }
            $sets[] = $k . '=' . $val;
        }
        $this->sql = 'UPDATE '. $table .' SET ' . implode(',', $sets);
        $this->bind = $this->bind($data);
    }
}