<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.02.16
 * Time: 11:44
 */

namespace app\tableGateways;

use app\db\Connection;
use app\db\Result;

abstract class AbstractGateway
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * AbstractGateway constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $sql
     * @param array|null $params
     * @return Result
     */
    public function query($sql, array $params = null)
    {
        return $this->connection->query($sql, $params);
    }

    /**
     * Обновление записей в таблице
     *
     * @param array $data
     * @param null $where
     * @return int
     */
    public function update(array $data, $where = null)
    {
        $setData = [];
        foreach ($data as $field => $value) {
            $setData[] = "$field = ?";
        }

        $tableName = $this->tableName();
        $set = implode(', ', $setData);

        $sql = "UPDATE \"$tableName\" SET $set";
        if ($where) {
            $sql .= " WHERE $where";
        }

        return $this->connection->execute($sql, array_values($data));
    }

    /**
     * Вставить новую запись в таблицу
     *
     * @param array $data
     * @return int
     */
    public function insert(array $data)
    {
        $tableName = $this->tableName();

        $fields = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO \"$tableName\" ($fields) VALUES ($values)";

        return $this->connection->execute($sql, array_values($data));
    }

    /**
     * Удаление записей
     *
     * @param string $where
     * @param array|null $params
     * @return int
     */
    public function delete($where, array $params = [])
    {
        $tableName = $this->tableName();

        $sql = "DELETE FROM \"$tableName\"";

        if ($where) {
            $sql .= " WHERE $where";
        }

        return $this->connection->execute($sql, $params);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Возвращает название таблицы
     *
     * @return string
     */
    abstract public function tableName();
}
