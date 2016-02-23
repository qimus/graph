<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 31.01.16
 * Time: 10:37
 */

namespace app\helpers;

/**
 * Class ArrayHelper
 * @package app\helpers
 */
class ArrayHelper
{
    /**
     * Возвращает значение массива по ключу, ключ можно указывать с учетом иерархии разделяя ключи точками
     * например, "config.api.version"
     *
     * @param array $arr
     * @param string $key
     * @param mixed $default
     * @return null
     */
    public static function getValue(array $arr, $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $arr;
        foreach ($keys as $field) {
            if (is_array($value) && isset($value[$field])) {
                $value = $value[$field];
            } else {
                $value = $default;
                break;
            }
        }

        return $value;
    }

    /**
     * Возвращает массив значений колонки массива
     *
     * @param array $arr
     * @param string $column
     * @param null $default
     * @return array
     */
    public static function getColumn(array $arr, $column, $default = null)
    {
        $result = array();
        foreach ($arr as $key => $value) {
            $result[] = self::getValue($arr[$key], $column, $default);
        }

        return $result;
    }

    /**
     * Поиск в коллекции используя callback
     *
     * @param array $collection
     * @param callable $what
     * @param bool $preserveKeys
     * @return array
     */
    private static function findByCallback(array $collection, callable $what, $preserveKeys = false)
    {
        $result = [];
        $pos = 0;
        foreach ($collection as $key => $item) {
            if (call_user_func($what, $item)) {
                $index = $preserveKeys ? $key : $pos++;
                $result[$index] = $item;
            }
        }
        return $result;
    }

    /**
     * Поиск в коллекции используя массив критерий
     *
     * @param array $collection
     * @param array $what
     * @param bool $preserveKeys
     * @return array
     */
    private static function findByArray(array $collection, array $what, $preserveKeys = false)
    {
        $result = [];
        $pos = 0;
        foreach ($collection as $key => $item) {
            $isFound = true;
            foreach ($what as $field => $value) {
                if (self::getValue($item, $field) != $value) {
                    $isFound = false;
                    break;
                }
            }
            if ($isFound) {
                $index = $preserveKeys ? $key : $pos++;
                $result[$index] = $item;
            }
        }

        return $result;
    }

    /**
     * Поиск в коллекции
     *
     * @param array $collection
     * @param array|callable $what
     * @param bool $preserveKeys
     * @return array
     */
    public static function find(array $collection, $what, $preserveKeys = false)
    {
        if (is_callable($what)) {
            return self::findByCallback($collection, $what, $preserveKeys);
        } elseif (is_array($what)) {
            return self::findByArray($collection, $what, $preserveKeys);
        } else {
            return [];
        }
    }

    /**
     * Возвращает первый элемент коллекции
     *
     * @param array $collection
     * @return mixed
     */
    public static function first(array $collection)
    {
        return reset($collection);
    }
}
