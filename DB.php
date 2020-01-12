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

    /**
     * @param string $table
     * @param string $select
     * @return Query\Select
     */
    public static function select($table, $select = '*')
    {
        return new Query\Select(self::defaultConn(), $table, $select);
    }

    /**
     * @param string $table
     * @param array $data
     * @return Query\Insert
     */
    public static function insert($table, $data)
    {
        return new Query\Insert(self::defaultConn(), $table, $data);
    }

    /**
     * @param string $table
     * @param array $data
     * @return Query\Update
     */
    public static function update($table, $data)
    {
        return new Query\Update(self::defaultConn(), $table, $data);
    }

    /**
     * @param string $table
     * @return Query\Delete
     */
    public static function delete($table)
    {
        return new Query\Delete(self::defaultConn(), $table);
    }
}