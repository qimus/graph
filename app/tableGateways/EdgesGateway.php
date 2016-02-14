<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.02.16
 * Time: 13:02
 */

namespace app\tableGateways;

use app\exceptions\Base;

class EdgesGateway extends AbstractGateway
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'edges';
    }

    /**
     * @param int $nodeId
     * @return mixed|null
     */
    public function findEdgeByNode($nodeId)
    {
        return $this
            ->query("SELECT * FROM edges WHERE start = :nodeId ORDER BY pos ASC", [':nodeId' => $nodeId])
            ->first();
    }

    /**
     * Проверка существования обратной связи для переданных узлов
     *
     * @param int $node1
     * @param int $node2
     * @return bool
     */
    public function hasLoopEdge($node1, $node2)
    {
        $result = $this
            ->query('SELECT * from edges WHERE start = :nodeStart AND "end" = :nodeEnd', [
                ':nodeStart' => $node2,
                ':nodeEnd' => $node1
            ]);

        return !$result->isEmpty();
    }

    /**
     * Добавить ребро
     *
     * @param int $nodeFrom
     * @param int $nodeTo
     * @throws Base
     */
    public function addEdge($nodeFrom, $nodeTo)
    {
        $existsEdges = $this->findEdgeByNode($nodeFrom);
        $pos = !empty($existsEdges['pos']) ? $existsEdges['pos'] + 1 : 1;

        if ($this->hasLoopEdge($nodeFrom, $nodeTo)) {
            throw new Base('Circle link detected');
        }

        $this->insert(['start' => $nodeFrom, '"end"' => $nodeTo, 'pos' => $pos]);
    }

    /**
     * Удаление связи между узлами графа
     *
     * @param int $nodeFrom
     * @param int $nodeTo
     *
     * @return int
     */
    public function removeEdge($nodeFrom, $nodeTo)
    {
        return $this->delete('start = :start AND "end" = :end', [':start' => $nodeFrom, ':end' => $nodeTo]);
    }

    /**
     * Поиск дочерних элементов
     *
     * @param $nodeId
     * @return array
     */
    public function findChildren($nodeId)
    {
        $sql = 'WITH RECURSIVE search_children(start, "end") AS (
                    SELECT e.start, e."end"
                    FROM edges e
                  UNION ALL
                    SELECT e.start, sg."end"
                    FROM edges e, search_children sg
                    WHERE e."end" = sg.start
                ) SELECT * from search_children WHERE start = :nodeId';


        return $this->query($sql, [':nodeId' => $nodeId])->fetchAll();
    }

    /**
     * Поиск родительских элементов
     *
     * @param int $nodeId
     * @return array
     */
    public function findParents($nodeId)
    {
        $sql = 'WITH RECURSIVE search_parent(start, "end") AS (
                    SELECT e.start, e."end"
                    FROM edges e
                  UNION ALL
                    SELECT sg.start, e."end"
                    FROM edges e, search_parent sg
                    WHERE sg."end" = e.start
                ) SELECT * from search_parent sp WHERE sp."end" = :nodeId';

        return $this->query($sql, [':nodeId' => $nodeId])->fetchAll();
    }

    /**
     * @param int[] $nodeIds
     * @return array
     */
    public function getByNodes($nodeIds)
    {
        $nodeIdsStr = implode(', ', array_map('intval', $nodeIds));

        return $this->query("SELECT * FROM edges WHERE start IN ($nodeIdsStr) AND \"end\" IN ($nodeIdsStr)")
            ->fetchAll();
    }
}
