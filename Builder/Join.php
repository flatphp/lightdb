<?php namespace Lightdb\Builder;


class Join extends SqlAbstract
{
    const TYPE_LEFT = 'LEFT';
    const TYPE_RIGHT = 'RIGHT';
    const TYPE_INNER = 'INNER';
    const TYPE_FULL = 'FULL';

    public function __construct($type, $table, $on, $bind = null)
    {
        $this->sql = $type .' JOIN '. $table .' ON '. $on;
        $this->bind = $this->bind($bind);
    }
}