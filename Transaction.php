<?php namespace Lightdb;


class Transaction
{
    /**
     * @var Conn
     */
    protected $conn;
    protected $txns = 0; // nested transactions
    protected $successes = [];
    protected $fails = [];

    public function __construct(Conn $conn)
    {
        $this->conn = $conn;
    }

    public function onCommit(callable $func)
    {
        $this->successes[] = $func;
    }

    public function onRollback(callable $func)
    {
        $this->fails[] = $func;
    }

    /**
     * @param callable $func
     * @return mixed
     * @throws \Exception|\Throwable
     */
    public function run(callable $func)
    {
        $this->beginTransaction();
        try {
            $res = $func($this->conn);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
        // if last commit then trigger event
        if ($this->txns == 0) {
            foreach ($this->successes as $func) {
                $func();
            }
        }
        return $res;
    }

    /**
     * Start a new database transaction.
     * @return void
     */
    public function beginTransaction()
    {
        if ($this->txns == 0) {
            $this->conn->getPDO()->beginTransaction();
        }
        $this->txns++;
    }

    /**
     * Commit the active database transaction.
     */
    public function commit()
    {
        if ($this->txns == 1) {
            $this->conn->getPDO()->commit();
        }
        $this->txns = max(0, $this->txns - 1);
    }

    /**
     * Rollback the active database transaction.
     * @return void
     */
    public function rollback()
    {
        if ($this->txns == 1) {
            $this->conn->getPDO()->rollBack();
            $this->txns = 0;
            foreach ($this->fails as $func) {
                $func();
            }
        } else {
            $this->txns--;
        }
    }

    /**
     * @return int
     */
    public function getTransactionLevel()
    {
        return $this->txns;
    }
}