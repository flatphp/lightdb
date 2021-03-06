<?php namespace Lightdb;


/**
 * @method static \PDO getPDO()
 * @method static Conn master()
 * @method static array fetchAll($sql, $bind = null)
 * @method static array fetchAllIndexed($sql, $bind = null)
 * @method static array fetchAllGrouped($sql, $bind = null)
 * @method static array fetchAllTo($name, $sql, $bind = null, callable $handle = null)
 * @method static array fetchAllIndexedTo($name, $sql, $bind = null, callable $handle = null)
 * @method static array fetchAllGroupedTo($name, $sql, $bind = null, callable $handle = null)
 * @method static array|false fetchRow($sql, $bind = null)
 * @method static object|null fetchRowTo($name, $sql, $bind = null, callable $handle = null)
 * @method static array fetchColumn($sql, $bind = null)
 * @method static array fetchColumnGrouped($sql, $bind = null)
 * @method static array fetchPairs($sql, $bind = null)
 * @method static array fetchPairsGrouped($sql, $bind = null)
 * @method static mixed fetchOne($sql, $bind = null)
 * @method static bool execute($sql, $bind = null, &$affected_rows = 0)
 * @method static string getLastInsertId()
 * @method static Query query(array $options = [])
 * @method static Transaction transaction()
 */
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