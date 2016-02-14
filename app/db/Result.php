<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.02.16
 * Time: 22:04
 */

namespace app\db;

use app\helpers\ArrayHelper;

class Result implements \Iterator
{
    /**
     * @var null|array
     */
    private $currentData = null;

    /**
     * @var array
     */
    private $resultRows = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $rowCount = 0;

    /**
     * Result constructor.
     * @param array $result
     */
    public function __construct(array $result)
    {
        $this->resultRows = $result;
        $this->rowCount = count($this->resultRows);
    }

    /**
     * Возвращает первый элемент из выборки
     * @return mixed
     */
    public function first()
    {
        return ArrayHelper::getValue($this->resultRows, 0);
    }

    /**
     * Возвращает последний элемент выборки
     *
     * @return mixed
     */
    public function last()
    {
        return ArrayHelper::getValue($this->resultRows, $this->rowCount - 1);
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        return $this->resultRows;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->rowCount;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->currentData;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->currentData = ArrayHelper::getValue($this->resultRows, $this->position, false);
        $this->position++;

        return $this->currentData;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->currentData !== false;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
        $this->currentData = $this->first();
    }
}
