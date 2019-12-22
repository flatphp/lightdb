<?php namespace Lightdb\Builder;

use Lightdb\Conn;

class BuilderAbstract extends SqlAbstract
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