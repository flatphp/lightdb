<?php namespace Lightdb\Query;

use Lightdb\Conn;

abstract class QueryAbstract
{
    /**
     * @var Conn
     */
    protected $conn;

    abstract protected function assemble();

    public function getLog()
    {
        return $this->assemble();
    }

    public function log()
    {
        print_r($this->getLog());
    }
}