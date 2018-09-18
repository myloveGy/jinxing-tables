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

function get_data($key = 'data')
{
    // 写入数据到SESSION
    if (!isset($_SESSION[$key])) {
        $data = [];
        for ($i = 1; $i < 100; $i++) {
            $data[] = [
                'id'         => $i,
                'name'       => 'Test -- ' . mt_rand(1000, 9999),
                'created_at' => time() - mt_rand(1000, 9999) * 2,
                'updated_at' => time(),
            ];
        }

        $_SESSION[$key] = $data;
    }

    return $_SESSION[$key];
}
