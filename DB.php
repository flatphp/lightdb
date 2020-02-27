<?php namespace Lightdb;


class DB
{
    protected static $conf;
    protected static $conns = [];

    public static function init(array $conf)
    {
        self::$conf = $conf;
    }

    /**
     * @param string|null $name
     * @return Conn
     */
    public static function conn($name = null)
    {
        if (!$name) {
            return self::defaultConn();
        }
        $conf = self::$conf[$name];
        if (!isset(self::$conns[$name])) {
            self::$conns[$name] = new Conn($conf);
        }
        return self::$conns[$name];
    }

    /**
     * @param string|null $name
     * @return Query
     */
    public static function query($conn = null, array $options = [])
    {
        if (!$conn instanceof Conn) {
            $conn = self::conn($conn);
        }
        return new Query($conn, $options);
    }

    /**
     * @return Conn
     */
    protected static function defaultConn()
    {
        static $conn = null;
        if (null === $conn) {
            $conn = new Conn(self::$conf);
        }
        return $conn;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args = [])
    {
        return call_user_func_array([self::defaultConn(), $method], $args);
    }
}