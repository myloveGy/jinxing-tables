<?php
// 设置头信息
header('Content-Type:text/html; charset=UTF-8');
session_start();

include './includes/data.php';

// 写入数据到SESSION
if (!isset($_SESSION['data'])) {
    $data = [];
    for ($i = 1; $i < 100; $i ++) {
        $data[] = [
            'id' => $i,
            'name' => 'Test -- '.mt_rand(1000, 9999)
        ];
    }

    $_SESSION['data'] = $data;
}

if ($_GET && isset($_GET['type']) && in_array($_GET['type'], ['create', 'search', 'update', 'delete'])) {
    $array = [
        'errCode' => 1,
        'errMsg' => '请求参数错误问题',
        'data' => null,
    ];

    switch ($_GET['type']) {
        case 'search':
            $intEcho = isset($_POST['sEcho']) ? (int)$_POST['sEcho'] : 0; // 请求次数
            $intStart = isset($_POST['iDisplayStart']) ? (int)$_POST['iDisplayStart'] : 0;     // 分页开始位置
            $intLength = isset($_POST['iDisplayLength']) ? (int)$_POST['iDisplayLength'] : 10; // 分页长度
            $data = isset($_SESSION['data']) ? $_SESSION['data'] : [];
            $arrData = array_splice($data, $intStart, $intLength);
            $array = [
                'errCode' => 0,
                'errMsg' => 'SUCCESS',
                'data' => [
                    'sEcho' => (int)$_POST['sEcho'],
                    'iTotalRecords' => count($arrData),
                    'iTotalDisplayRecords' => count($data),
                    'aaData' => $arrData
                ],
            ];
            break;
        case 'create':
            $_SESSION['data'] = dataCreate($_SESSION['data'], $_POST);
            $array = [
                'errCode' => 0,
                'errMsg' => 'SUCCESS'
            ];
            break;
        case 'update':
            $_SESSION['data'] = dataUpdate($_SESSION['data'], $_POST['id'], $_POST);
            $array = [
                'errCode' => 0,
                'errMsg' => 'SUCCESS'
            ];
            break;
        case 'delete':
            $_SESSION['data'] = dataDelete($_SESSION['data'], $_POST['id']);
            $array = [
                'errCode' => 0,
                'errMsg' => 'SUCCESS'
            ];
            break;
    }

    exit(json_encode($array, 320));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title> meTables -- 使用示例 </title>
    <!-- Bootstrap core CSS -->
    <link href="./public/css/bootstrap.min.css" rel="stylesheet">
    <link href="./public/css/font-awesome.min.css" rel="stylesheet">
    <style type="text/css">
        div.main {margin-top:50px;}
        p.bg-success {padding:10px;}
        .m-coll {margin-top:3px;}
        .isHide {display:none}
    </style>
</head>
<body role="document">
<!-- Fixed navbar -->
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/"> meTables -- 使用示例 </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav pull-right">
                <li class="active"><a href="/">首页</a></li>
                <li><a href="./create.php">创建视图文件</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container theme-showcase main" role="main">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <h1> 简单例子 </h1>
                <p id="me-table-buttons"></p>
                <table class="table table-striped table-bordered table-hover" id="show-table"></table>
            </div>
        </div>
    </div>
</div>
<!-- 加载公共js -->
<script src="./public/js/jquery.min.js"></script>
<script src="./public/js/bootstrap.min.js"></script>
<script src="./public/js/jquery.dataTables.min.js"></script>
<script src="./public/js/jquery.dataTables.bootstrap.js"></script>
<script src="./public/js/jquery.validate.min.js"></script>
<script src="./public/js/meTables.js"></script>
<script src="./public/js/layer/layer.js"></script>
<script type="text/javascript">
    var m = meTables({
        title: "示例",
        url: {
            search: "./index.php?type=search",
            create: "./index.php?type=create",
            update: "./index.php?type=update",
            delete: "./index.php?type=delete"
        },
        table: {
            "aoColumns":[
                {"title": "id", "data": "id", "sName": "id",  "defaultOrder": "desc",
                    "edit": {"type": "hidden"}
                },
                {"title": "名称", "data": "name", "sName": "name",
                    "edit": {"type": "text", "required": true, "rangelength":"[2, 40]"},
                    "search": {"type": "text"},
                    "bSortable": false
                }
            ]
        }
    });

    $(function(){
        m.init();
    })
</script>
</body>
</html>
