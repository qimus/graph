<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.02.16
 * Time: 11:43
 */

namespace app\tableGateways;

class NodesGateway extends AbstractGateway
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'nodes';
    }

    /**
     * Поиск узла по имени
     *
     * @param string $name
     * @return null
     */
    public function getNodeIdByName($name)
    {
        $node = $this
            ->query('SELECT * FROM nodes WHERE "name" = :name', [':name' => $name])
            ->first();

        return $node ? $node['id'] : null;
    }

    /**
     * Добавление новой ноды в граф
     *
     * @param string $name
     */
    public function addNode($name)
    {
        $this->insert(['name' => $name]);
    }

    /**
     * Удаление ноды
     *
     * @param int $id
     * @return int
     */
    public function deleteNode($id)
    {
        return $this->delete("id = :id", [':id' => $id]);
    }

    /**
     * Поиск узлом по id
     *
     * @param array|int $ids
     * @return \PDOStatement
     */
    public function getAllByIds($ids)
    {
        $ids = array_map('intval', (array)$ids);
        $ids = implode(',', $ids);

        return $this->query("SELECT * FROM nodes WHERE id IN ($ids)")->fetchAll();
    }
}
