<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17 0017
 * Time: 下午 8:45
 */
session_start();

include './includes/data.php';
include './includes/functions.php';

if (($type = get('type')) && in_array($type, ['create', 'search', 'update', 'delete'])) {
    switch ($type) {
        case 'search':
            $offset  = (int)get('offset', 0); // 查询开始位置
            $limit   = (int)get('limit', 10); // 查询长度
            $draw    = (int)get('draw', 0);   // 请求次数
            $data    = get_data();
            $arrData = array_slice($data, $offset, $limit);
            echo_json([
                'draw'            => $draw,
                'recordsTotal'    => count($data),
                'recordsFiltered' => count($data),
                'data'            => $arrData
            ]);
            break;
        case 'create':
            $_SESSION['data'] = dataCreate($_SESSION['data'], $_POST);
            success($_POST);
            break;
        case 'update':
            $_SESSION['data'] = dataUpdate($_SESSION['data'], $_POST['id'], $_POST);
            success($_POST);
            break;
        case 'delete':
            $_SESSION['data'] = dataDelete($_SESSION['data'], $_POST['id']);
            success($_POST);
            break;
    }

    error(405, '请求失败');
}
?>