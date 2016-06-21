<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 12:22
 */

namespace App\NotosDatabase;


use PDO;
use PDOException;

class PdoAdapter implements DatabaseAdapterInterface
{
    protected $config = [];
    protected $connection;
    protected $statement;
    protected $fetchMode = PDO::FETCH_ASSOC;

    public function __construct($dsn, $username = null, $password = null, array $driverOptions = [])
    {
        $this->config = compact('dsn', 'username', 'password', 'driverOptions');
    }

    public function getStatement()
    {
        if ($this->statement === null) {
            throw new \PDOException("There is no PDOStatement object for use.");
        }
        return $this->statement;
    }

    public function connect()
    {
        if ($this->connection) {
            return;
        }
        try {
            $this->connection = new PDO(
                $this->config['dsn'],
                $this->config['username'],
                $this->config['password'],
                $this->config['driverOptions']
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function disconnect()
    {
        $this->connection = null;
    }

    public function prepare($sql, array $options = [])
    {
        $this->connect();
        try {
            $this->statement = $this->connection->prepare($sql, $options);
            return $this;
        } catch (PDOException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function execute(array $parameters = [])
    {
        try {
            $this->getStatement()->execute($parameters);
            return $this;
        } catch (PDOException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function countAffectedRows()
    {
        try {
            return $this->getStatement()->rowCount();
        } catch (PDOException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function fetch($fetchStyle = null,
                          $cursorOrientation = null, $cursorOffset = null)
    {
        if ($fetchStyle === null) {
            $fetchStyle = $this->fetchMode;
        }
        try {
            return $this->getStatement()->fetch($fetchStyle, $cursorOrientation, $cursorOffset);
        } catch (PDOException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function fetchAll($fetchStyle = null, $column = 0)
    {
        if ($fetchStyle === null) {
            $fetchStyle = $this->fetchMode;
        }

        try {
            return $fetchStyle === PDO::FETCH_COLUMN
                ? $this->getStatement()->fetchAll($fetchStyle, $column)
                : $this->getStatement()->fetchAll($fetchStyle);
        } catch (PDOException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function select($table, array $bind, $boolOperator = 'AND')
    {
        if ($bind) {
            $where = [];
            foreach ($bind as $col => $value) {
                unset($bind[$col]);
                $bind[':' . $col] = $value;
                $where [] = $col . ' = :' . $col;
            }
        }

        $sql = 'SELECT * FROM ' . $table
            . (($bind) ? ' WHERE '
                . implode(' ' . $boolOperator . ' ', $where) : ' ');
        $this->prepare($sql)
            ->execute($bind);
        return $this;
    }

    public function insert($table, array $bind)
    {
        $cols = implode(', ', array_keys($bind));
        $values = implode(', :', array_keys($bind));
        foreach ($bind as $col => $value) {
            unset($bind[$col]);
            $bind[':' . $col] = $value;
        }

        $sql = 'INSERT INTO ' . $table
            . ' (' . $cols . ') VALUES(:' . $values . ')';
        return $this->prepare($sql)
            ->execute($bind)
            ->countAffectedRows();
    }

    public function update($table, array $bind, $where = "")
    {
        $set = [];
        foreach ($bind as $col => $value) {
            unset($bind[$col]);
            $bind[':' . $col] = $value;
            $set[] = $col . ' = :' . $col;
        }

        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $set)
            . (($where) ? ' WHERE ' . $where : ' ');
        return $this->prepare($sql)
            ->execute($bind)
            ->countAffectedRows();
    }

    public function delete($table, $where = "")
    {
        $sql = 'DELETE FROM ' . $table . (($where) ? ' WHERE ' . $where : ' ');
        return $this->prepare($sql)
            ->execute()
            ->countAffectedRows();
    }
}