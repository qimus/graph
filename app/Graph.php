<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.02.16
 * Time: 13:14
 */

namespace app;

use app\exceptions\Base;
use app\exceptions\DatabaseException;
use app\exceptions\GraphException;
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

    /**
     * Смена порядка узла
     *
     * @param int $parentId
     * @param int $nodeId
     * @param int $position
     * @return bool|int
     *
     * @throws GraphException
     */
    public function changePosition($parentId, $nodeId, $position)
    {
        $directChilds = $this->edgesGateway->findDirectChildren($parentId);

        if (empty($directChilds)) {
            throw new GraphException('Child nodes not found.');
        }

        $childNodeIds = ArrayHelper::getColumn($directChilds, 'end');
        if (!in_array($nodeId, $childNodeIds)) {
            throw new GraphException('Specified node is not a child.');
        }

        $oldNodeIdHasSamePosition = null;  //id ноды имеющей переданную позицию
        $currentPositionNodeId = null; //текущая позиция узла
        foreach ($directChilds as $child) {
            //уже есть дочерний узел с переданной позицией
            if ($child['pos'] == $position) {
                $oldNodeIdHasSamePosition = $child['end'];
            } elseif ($child['end'] == $nodeId) {
                $currentPositionNodeId = $child['pos'];
            }
        }

        //переданная позиция является текущей для узла
        if ($currentPositionNodeId == $position) {
            return true;
        }

        $positions = [
            [
                'start' => $parentId,
                'end' => $nodeId,
                'pos' => $position
            ]
        ];

        if ($oldNodeIdHasSamePosition) {
            $positions[] = [
                'start' => $parentId,
                'end' => $oldNodeIdHasSamePosition,
                'pos' => $currentPositionNodeId
            ];
        }

        $this->edgesGateway->updatePositions($positions);

        return true;
    }

    /**
     * Поменять позиции местами
     *
     * @param int $parentId
     * @param int $nodeId1
     * @param int $nodeId2
     * @throws DatabaseException
     * @throws GraphException
     */
    public function swapPositions($parentId, $nodeId1, $nodeId2)
    {
        $directChilds = $this->edgesGateway->findDirectChildren($parentId);

        if (empty($directChilds)) {
            throw new GraphException('Child nodes not found.');
        }

        $childNodeIds = ArrayHelper::getColumn($directChilds, 'end');
        if (!in_array($nodeId1, $childNodeIds) || !in_array($nodeId2, $childNodeIds)) {
            throw new GraphException('Specified node is not a child.');
        }

        $sourcePosition = ArrayHelper::first(ArrayHelper::find($directChilds, ['end' => $nodeId1]));
        $targetPosition = ArrayHelper::first(ArrayHelper::find($directChilds, ['end' => $nodeId2]));

        $tmpPos = $sourcePosition['pos'];
        $sourcePosition['pos'] = $targetPosition['pos'];
        $targetPosition['pos'] = $tmpPos;

        $this->edgesGateway->updatePositions([$sourcePosition, $targetPosition]);
    }
}
