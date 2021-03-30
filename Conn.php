<?php namespace Lightdb;

use PDO;
use PDOStatement;

class Conn
{
    protected $conf_master;
    protected $conf_slaves = [];
    protected $options = [];
    protected $pdo_master;
    protected $pdo_slave;
    protected $force = false;
    protected $transaction;

    /**
     * [
     *   'dsn' => 'mysql:host=localhost;dbname=testdb',
     *   'username' => 'root',
     *   'password' => '123456',
     *   'options' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
     *   'slaves' => array(
     *     [...],
     *   )
     * ]
     * @param array $conf
     */
    public function __construct(array $conf)
    {
        if (isset($conf['slaves'])) {
            $this->conf_slaves = $conf['slaves'];
            unset($conf['slaves']);
        }
        if (isset($conf['options'])) {
            $this->options = $conf['options'];
            unset($conf['options']);
        }
        $this->conf_master = $conf;
    }


    /**
     * @param array $conf
     * @param array $options
     * @return PDO
     */
    protected function connect(array $conf, array $options = [])
    {
        $options = array_diff_key([PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION], $options) + $options;
        return new PDO($conf['dsn'], $conf['username'], $conf['password'], $options);
    }

    /**
     * get master connection
     * @return PDO
     */
    protected function masterPDO()
    {
        if (null === $this->pdo_master) {
            $this->pdo_master = $this->connect($this->conf_master, $this->options);
        }
        return $this->pdo_master;
    }

    /**
     * get slave connection
     * @return PDO
     */
    protected function slavePDO()
    {
        if (empty($this->conf_slaves) || $this->txns > 0 || $this->force) {
            return $this->masterPDO();
        }
        if (null === $this->pdo_slave) {
            $i = mt_rand(0, count($this->conf_slaves)-1);
            $conf = $this->conf_slaves[$i];
            if (isset($conf['options'])) {
                $options = array_diff_key($this->options, $conf['options']) + $conf['options'];
            } else {
                $options = $this->options;
            }
            $this->pdo_slave = $this->connect($conf, $options);
        }
        return $this->pdo_slave;
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->masterPDO();
    }

    /**
     * force use masterPDO
     */
    public function master()
    {
        $this->force = true;
        return $this;
    }


    /**
     * fetch all array with assoc, empty array returned if nothing or false
     * @param string $sql
     * @param mixed $bind
     * @return array
     */
    public function fetchAll($sql, $bind = null)
    {
        return $this->selectPrepare($sql, $bind)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * fetch all with first field as indexed key, empty array returned if nothing or false
     * @param string $sql
     * @param mixed $bind
     * @return array
     */
    public function fetchAllIndexed($sql, $bind = null)
    {
        return $this->selectPrepare($sql, $bind)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * fetch all grouped array with first field as keys, empty array returned if nothing or false
     * @param string $sql
     * @param mixed $bind
     * @return array
     */
    public function fetchAllGrouped($sql, $bind = null)
    {
        return $this->selectPrepare($sql, $bind)->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
    }

    /**
     * fetch all to classes array, empty array returned if nothing or false
     * 由于PDO::FETCH_CLASS可控性差，因此通过构造实例化对象
     * @param string $name
     * @param string $sql
     * @param mixed $bind
     * @param callable $handle
     * @return array
     */
    public function fetchAllTo($name, $sql, $bind = null, callable $handle = null)
    {
        return array_map(function ($item) use($name, $handle) {
            return $this->newObj($name, $item, $handle);
        }, $this->fetchAll($sql, $bind));
    }

    /**
     * fetch all to classes array with firest field as indexed key, empty array returned if nothing or false
     * @param string $name
     * @param string $sql
     * @param mixed $bind
     * @param callable $handle
     * @return array
     */
    public function fetchAllIndexedTo($name, $sql, $bind = null, callable $handle = null)
    {
        return array_map(function ($item) use($name, $handle) {
            return $this->newObj($name, $item, $handle);
        }, $this->fetchAllIndexed($sql, $bind));
    }

    /**
     * fetch all grouped array with first field as keys, empty array returned if nothing or false
     * @param string $name
     * @param string $sql
     * @param mixed $bind
     * @param callable $handle
     * @return array
     */
    public function fetchAllGroupedTo($name, $sql, $bind = null, callable $handle = null)
    {
        return array_map(function ($items) use($name, $handle){
            return array_map(function($item) use($name, $handle){
                return $this->newObj($name, $item, $handle);
            }, $items);
        }, $this->fetchAllGrouped($sql, $bind));
    }

    /**
     * fetch one row array with assoc, false is returned if failure
     * @param string $sql
     * @param mixed $bind
     * @return array|false
     */
    public function fetchRow($sql, $bind = null)
    {
        return $this->selectPrepare($sql, $bind)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * fetch one row to class, null is returned if failure
     * @param string $name
     * @param string $sql
     * @param mixed $bind
     * @param callable $handle
     * @return object|false
     */
    public function fetchRowTo($name, $sql, $bind = null, callable $handle = null)
    {
        $data = $this->fetchRow($sql, $bind);
        if (empty($data)) {
            return null;
        }
        return $this->newObj($name, $data, $handle);
    }


    protected function newObj($name, $data, callable $handle = null)
    {
        $obj = new $name($data);
        if (isset($handle)) {
            $obj = $handle($obj);
        }
        return $obj;
    }

    /**
     * fetch first column array, empty array returned if nothing or false
     * @param string $sql
     * @param mixed $bind
     * @return array
     */
    public function fetchColumn($sql, $bind = null)
    {
        return $this->selectPrepare($sql, $bind)->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * fetch grouped column array with first field as keys of grouped array, empty array returned if nothing or false
     * @param string $sql
     * @param mixed $bind
     * @return array
     */
    public function fetchColumnGrouped($sql, $bind = null)
    {
        $data = [];
        foreach ($this->selectPrepare($sql, $bind)->fetchAll(PDO::FETCH_NUM) as $row) {
            $data[$row[0]][] = $row[1];
        }
        return $data;
    }

    /**
     * fetch pairs of first column as Key and second column as Value, empty array returned if nothing or false
     * @param string $sql
     * @param mixed $bind
     * @return array
     */
    public function fetchPairs($sql, $bind = null)
    {
        return $this->selectPrepare($sql, $bind)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * fetch grouped pairs of K/V with first field as keys of grouped array, empty array returned if nothing of false
     * @param string $sql
     * @param mixed $bind
     * @return array
     */
    public function fetchPairsGrouped($sql, $bind = null)
    {
        $data = [];
        foreach ($this->selectPrepare($sql, $bind)->fetchAll(PDO::FETCH_NUM) as $row) {
            $data[$row[0]][$row[1]] = $row[2];
        }
        return $data;
    }

    /**
     * fetch one column value, false returned if nothing or false
     * @param string $sql
     * @param mixed $bind
     * @return mixed
     */
    public function fetchOne($sql, $bind = null)
    {
        return $this->selectPrepare($sql, $bind)->fetchColumn(0);
    }

    /**
     * @param string $sql
     * @param mixed $bind
     * @param int $fetch_mode
     * @return PDOStatement
     */
    public function selectPrepare($sql, $bind = null, $fetch_mode = null, $fetch_arg = null)
    {
        $stmt = $this->slavePDO()->prepare($sql);
        if (null !== $fetch_mode) {
            $stmt->setFetchMode($fetch_mode, $fetch_arg);
        }
        $stmt->execute($this->bind($bind));
        $this->force = false; // reset force
        return $stmt;
    }

    /**
     * Execute an SQL statement and return the boolean result.
     * @param string $sql
     * @param mixed $bind
     * @param int &$affected_rows
     * @return bool
     */
    public function execute($sql, $bind = null, &$affected_rows = 0)
    {
        $stmt = $this->masterPDO()->prepare($sql);
        $res = $stmt->execute($this->bind($bind));
        $affected_rows = $stmt->rowCount();
        return $res;
    }

    /**
     * Get last insert id
     * @return int|string
     */
    public function getLastInsertId()
    {
        return $this->masterPDO()->lastInsertId();
    }


    /**
     * get transaction
     * @return Transaction
     */
    public function transaction()
    {
        if (null === $this->transaction) {
            $this->transaction = new Transaction($this);
        }
        return $this->transaction;
    }


    /**
     * get new query instance based this connection
     * @param array $options
     * @return Query
     */
    public function query(array $options = [])
    {
        return new Query($this, $options);
    }


    /**
     * Parse bind as array
     * @param mixed $bind
     * @return null|array
     */
    protected function bind($bind)
    {
        if ($bind === null) {
            return null;
        }
        if (!is_array($bind)) {
            $bind = [$bind];
        }
        return array_values($bind);
    }
}
