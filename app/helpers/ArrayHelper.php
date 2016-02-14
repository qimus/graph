<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 31.01.16
 * Time: 10:37
 */

namespace app\helpers;

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
}
