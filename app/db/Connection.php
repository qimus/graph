<?php
namespace app\db;

class Connection
{
    const QUERY_TYPE_SELECT = 'select';
    const QUERY_TYPE_UPDATE = 'update';
    const QUERY_TYPE_INSERT = 'insert';
    const QUERY_TYPE_DELETE = 'delete';

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
     * @throws DbException
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
            throw new DbException($this->connection->errorInfo()[2]);
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
     */
    public function execute($query, array $params = null)
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
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
}
