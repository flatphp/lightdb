<?php namespace Lightdb\Query;

use Lightdb\Conn;

class QueryAbstract extends SqlAbstract
{
    /**
     * @var Conn
     */
    protected $conn;

    public function log()
    {
        print_r(array(
            'sql' => $this->getSql(),
            'bind' => $this->getBind()
        ));
    }
}