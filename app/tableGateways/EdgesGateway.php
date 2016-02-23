<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.02.16
 * Time: 13:02
 */

namespace app\tableGateways;

use app\exceptions\Base;
use app\exceptions\DatabaseException;
use app\exceptions\LoopException;

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
    public function findLastNodeEdge($nodeId)
    {
        return $this
            ->query("SELECT * FROM edges WHERE start = :nodeId ORDER BY pos DESC LIMIT 1", [':nodeId' => $nodeId])
            ->first();
    }

    /**
     * Проверка существования обратной связи для переданных узлов
     *
     * @param int $from
     * @param int $to
     *
     * @return int
     */
    public function hasLoop($from, $to)
    {
        if ($from == $to) {
            return 1;
        }

        $sql = 'WITH RECURSIVE r as (
                    SELECT "start", "end"
                    FROM edges
                    WHERE "end" = :from
                  UNION
                      SELECT e.start, e."end"
                      FROM r, edges e
                      WHERE r.start = e."end"
                )
                SELECT count(*) FROM r WHERE r.start = :to;';

        return $this->query($sql, [':from' => $from, ':to' => $to])->scalar();
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
        $existsEdges = $this->findLastNodeEdge($nodeFrom);
        $pos = !empty($existsEdges['pos']) ? $existsEdges['pos'] + 1 : 1;

        if ($this->hasLoop($nodeFrom, $nodeTo)) {
            throw new LoopException('Circle link detected');
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
     * @param int $nodeId
     * @return array
     */
    public function findChildren($nodeId)
    {
        $sql = 'WITH RECURSIVE r(start, "end", pos) AS (
                      SELECT e.start, e."end", pos, 1 as level
                          FROM edges e
                          WHERE e.start = :nodeId
                      UNION ALL
                          SELECT e.start, e."end", e.pos, level + 1 as level
                          FROM edges e, r
                          WHERE e.start = r."end"
                    ) SELECT * from r ORDER BY level, pos';


        return $this->query($sql, [':nodeId' => $nodeId])->fetchAll();
    }

    /**
     * Возвращает прямых потомков
     *
     * @param int $nodeId
     * @return array
     */
    public function findDirectChildren($nodeId)
    {
        $sql = 'SELECT * FROM edges WHERE start=:nodeId ORDER BY pos';

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
     * @param int $start
     * @param int $end
     * @param int $pos
     * @return int
     */
    public function updatePosition($start, $end, $pos)
    {
        $start = (int)$start;
        $end = (int)$end;

        return $this->update(['pos' => $pos], "start = $start AND \"end\" = $end");
    }

    /**
     * Обновление нескольких позиций
     *
     * @param array $positions
     * @throws DatabaseException
     */
    public function updatePositions(array $positions)
    {
        $this->getConnection()->beginTransaction();

        try {
            foreach ($positions as $positionData) {
                $this->updatePosition($positionData['start'], $positionData['end'], $positionData['pos']);
            }
        } catch (DatabaseException $e) {
            $this->getConnection()->rollBack();
            throw new DatabaseException($e->getMessage());
        }

        $this->connection->commit();
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
