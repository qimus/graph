<?php
namespace app\db;

use app\exceptions\DatabaseException;

class Connection
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var string
     */
    private $lastQuery;


    /**
     * Database constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Возвращает строку для подключения
     * @return string
     */
    private function getDsnString()
    {
        extract(array_merge([
            'user' => null,
            'password' => null,
            'host' => 'localhost',
            'port' => 5432
        ], $this->config));

        return "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    }

    /**
     * Подключение к базе
     */
    protected function connect()
    {
        if ($this->isConnected()) {
            return;
        }

        try {
            $this->connection = new \PDO($this->getDsnString());
        } catch (\PDOException $e) {
            throw new DbException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return !is_null($this->connection);
    }

    /**
     * Экранирование значения
     *
     * @param mixed $value
     * @return string
     */
    public function quoteValue($value)
    {
        return $this->connection->quote($value);
    }

    /**
     * Выполнение запроса
     *
     * @param string $sql
     * @param array $params
     * @return Result
     * @throws DatabaseException
     */
    public function query($sql, array $params = null)
    {
        if (!empty($params)) {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->connection->query($sql);
        }
        $this->lastQuery = $sql;

        if (false === $stmt) {
            throw new DatabaseException($this->connection->errorInfo()[2]);
        }

        return $this->createResult($stmt);
    }

    /**
     * @param \PDOStatement $stmt
     * @return Result
     */
    protected function createResult(\PDOStatement $stmt)
    {
        return new Result($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * Выполняет запрос и возвращает кол-во затронутых записей
     *
     * @param string $query
     * @param array $params
     * @return int
     * @throws DatabaseException
     */
    public function execute($query, array $params = null)
    {
        $stmt = $this->connection->prepare($query);
        $res = $stmt->execute($params);

        if (false === $res) {
            throw new DatabaseException($stmt->errorInfo()[2]);
        }

        $this->lastQuery = $stmt->queryString;

        return $stmt->rowCount();
    }

    /**
     * @return string
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * Стартуем транзакцию
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * Коммит транзакции
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * Откат транзакции
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }
}
