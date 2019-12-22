<?php namespace Lightdb\Builder;


abstract class SqlAbstract
{
    protected $sql;
    protected $bind = [];

    public function getSql()
    {
        return $this->sql;
    }

    public function getBind()
    {
        return $this->bind;
    }

    protected function bind($bind)
    {
        if ($bind === null) {
            return [];
        }
        if (is_callable($bind)) {
            $bind = $bind();
        }
        if (!is_array($bind)) {
            $bind = [$bind];
        }
        return array_values($bind);
    }
}