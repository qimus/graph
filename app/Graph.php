<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.02.16
 * Time: 13:14
 */

namespace app;

use app\helpers\ArrayHelper;
use app\tableGateways\EdgesGateway;
use app\tableGateways\NodesGateway;

class Graph
{
    /**
     * @var NodesGateway
     */
    private $nodesGateway;

    /**
     * @var EdgesGateway
     */
    private $edgesGateway;

    /**
     * Graph constructor.
     * @param NodesGateway $nodesGateway
     * @param EdgesGateway $edgesGateway
     */
    public function __construct(NodesGateway $nodesGateway, EdgesGateway $edgesGateway)
    {
        $this->nodesGateway = $nodesGateway;
        $this->edgesGateway = $edgesGateway;
    }

    /**
     * Добавить новый узел
     *
     * @param string $name
     */
    public function addNode($name)
    {
        $this->nodesGateway->insert(['name' => $name]);
    }

    /**
     * Удаление узла графа
     *
     * @param int $id
     */
    public function removeNode($id)
    {
        $this->nodesGateway->deleteNode($id);
    }

    /**
     * Добавить связь между узлами графа
     *
     * @param int $node1
     * @param int $node2
     */
    public function addLink($node1, $node2)
    {
        $this->edgesGateway->addEdge($node1, $node2);
    }

    /**
     * @param int $node1
     * @param int $node2
     */
    public function removeLink($node1, $node2)
    {
        $this->edgesGateway->removeEdge($node1, $node2);
    }

    /**
     * Метод возвращает всех потомков переданного узла
     *
     * @param int $nodeId
     * @return \PDOStatement
     */
    public function getChilds($nodeId)
    {
        $childs = $this->edgesGateway->findChildren($nodeId);

        if (empty($childs)) {
            return [];
        }

        return $this->nodesGateway->getAllByIds(ArrayHelper::getColumn($childs, 'end'));
    }

    /**
     * Метод возвращает всех предков переданного узла
     *
     * @param int $nodeId
     * @return array|\PDOStatement
     */
    public function getParents($nodeId)
    {
        $parents = $this->edgesGateway->findParents($nodeId);

        if (empty($parents)) {
            return [];
        }

        return $this->nodesGateway->getAllByIds(ArrayHelper::getColumn($parents, 'start'));
    }

    /**
     * Принадлежит ли узел всем переданным родителям
     *
     * @param int $nodeId
     * @param array $parentIds
     * @return bool
     */
    public function isNodeParents($nodeId, array $parentIds)
    {
        $parents = $this->edgesGateway->findParents($nodeId);

        if (empty($parents)) {
            return false;
        }

        $nodeParents = array_intersect($parentIds, ArrayHelper::getColumn($parents, 'start'));

        return count($nodeParents) == count($parentIds);
    }
}
