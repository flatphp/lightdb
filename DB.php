<?php namespace Lightdb;


class DB
{
    protected static $conf;
    protected static $conn;

    public static function init(array $conf)
    {
        self::$conf = $conf;
    }

    /**
     * @return Conn
     */
    public static function getConn()
    {
        if (null === self::$conn) {
            self::$conn = new Conn(self::$conf);
        }
        return self::$conn;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args = [])
    {
        return call_user_func_array([self::getConn(), $method], $args);
    }
}