<?php
/**
 * Created by PhpStorm.
 * User: liujinxing
 * Date: 2017/5/8
 * Time: 17:03
 */

/**
 * @param $data
 * @param $id
 *
 * @return bool|int|string
 */
function find($data, $id)
{
    $mixReturn = false;
    foreach ($data as $key => $value) {
        if ($value['id'] == $id) {
            $mixReturn = $key;
            break;
        }
    }

    return $mixReturn;
}

function dataCreate(&$data, $update)
{
    $update['id'] = mt_rand(10000, 99999);
    array_push($data, $update);
    return $data;
}

function dataUpdate($data, $id, $update)
{
    $key = find($data, $id);
    if ($key !== false) {
        $data[$key] = $update;
    }

    return $data;
}

function dataDelete($data, $id)
{
    $key = find($data, $id);
    if ($key !== false) {
        unset($data[$key]);
    }

    return $data;
}