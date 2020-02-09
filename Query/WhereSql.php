<?php namespace Lightdb\Query;


class WhereSql extends SqlAbstract
{
    public function where($where, $bind = null)
    {
        return $this->assemble('AND', $where, $bind);
    }

    public function orWhere($where, $bind = null)
    {
        return $this->assemble('OR', $where, $bind);
    }

    protected function assemble($type, $where, $bind = null)
    {
        if ($this->sql) {
            $this->sql .= " {$type} ";
        }
        if (is_callable($where)) {
            /** @var WhereSql $sub */
            $sub = $where(new WhereSql());
            $this->sql .= '('. $sub->getSql() .')';
            $bind = $sub->getBind();
        } else {
            $bind = $this->bind($bind);
            $this->sql .= $this->parse($where, $bind);
        }
        if (!empty($bind)) {
            $this->bind = array_merge($this->bind, $bind);
        }
        return $this;
    }

    /**
     * parse where in
     * example: ('id in ??', [1,2,3])
     */
    protected function parse($where, $bind)
    {
        if (strpos($where, '??') && preg_match('/\sin\s+\?\?/i', $where)) {
            $ex = implode(',', array_fill(0, count($bind), '?'));
            $where = str_replace('??', '('. $ex .')', $where);
        }
        return $where;
    }
}