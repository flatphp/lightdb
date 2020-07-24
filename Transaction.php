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

    /**
     * @param callable $func
     * @param callable|null $success
     * @param callable|null $fail
     * @return mixed
     * @throws \Exception|\Throwable
     */
    public function run(callable $func, callable $success = null, callable $fail = null)
    {
        if (null !== $success) {
            $this->successes[] = $success;
        }
        if (null !== $fail) {
            $this->fails[] = $fail;
        }
        $this->beginTransaction();
        try {
            $res = $func($this);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            $this->handleFail();
            throw $e;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->handleFail();
            throw $e;
        }
        $this->handleSuccess();
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
        }
        $this->txns = max(0, $this->txns - 1);
    }

    /**
     * @return int
     */
    public function getTransactionLevel()
    {
        return $this->txns;
    }

    /**
     * trigger success event after commit performed
     */
    protected function handleSuccess()
    {
        if ($this->txns == 0) {
            foreach ($this->successes as $func) {
                $func();
            }
        }
    }

    /**
     * trigger fail event after rollback performed
     */
    protected function handleFail()
    {
        if ($this->txns == 0) {
            foreach ($this->fails as $func) {
                $func();
            }
        }
    }
}