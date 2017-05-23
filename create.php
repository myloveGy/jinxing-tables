<?php
// 设置头信息
header('Content-Type:text/html; charset=UTF-8');

$arrConfig = [
    'user' => 'root',
    'password' => 'gongyan',
    'dbname' => 'test',
];

/**
 * createForm() 生成表格配置表单信息
 * @access private
 * @param  array  $array  数据表信息
 * return  string 返回HTML
 */
function createForm($array)
{
    $strHtml = '';
    foreach ($array as $value) {
        $key     = $value['Field'];
        $sTitle  = isset($value['Comment']) && ! empty($value['Comment']) ? $value['Comment'] : $value['Field'];
        $sOption = isset($value['Null']) && $value['Null'] == 'NO' ? '"required": true,' : '';
        if (stripos($value['Type'], 'int(') !== false) $sOption .= '"number": true,';
        if (stripos($value['Type'], 'varchar(') !== false) {
            $sLen = trim(str_replace('varchar(', '', $value['Type']), ')');
            $sOption .= '"rangelength": "[2, '.$sLen.']"';
        }

        $sOther = stripos($value['Field'], '_at') !== false ? 'mt.dateTimeString' : '';

        $strHtml .= <<<HTML
<div class="alert alert-success me-alert-su">
    <span class="label label-success me-label-sp">{$key}</span>
    <label class="me-label">标题: <input type="text" name="attr[{$key}][title]" value="{$sTitle}" required="true"/></label>
    <label class="me-label">编辑：
        <select class="is-hide" name="attr[{$key}][edit]">
            <option value="1" selected="selected">开启</option>
            <option value="0" >关闭</option>
        </select>
        <select name="attr[{$key}][type]">
            <option value="text" selected="selected">text</option>
            <option value="hidden">hidden</option>
            <option value="select">select</option>
            <option value="radio">radio</option>
            <option value="password">password</option>
            <option value="textarea">textarea</option>
        </select>
        <input type="text" name="attr[{$key}][options]" value='{$sOption}'/>
    </label>
    <label class="me-label">搜索：
        <select name="attr[{$key}][search]">
            <option value="1">开启</option>
            <option value="0" selected="selected">关闭</option>
        </select>
    </label>
    <label class="me-label">排序：<select name="attr[{$key}][bSortable]">
        <option value="1" >开启</option>
        <option value="0" selected="selected">关闭</option>
    </select></label>
    <label class="me-label">回调：<input type="text" name="attr[{$key}][createdCell]" value="{$sOther}" /></label>
</div>
HTML;
    }

    return $strHtml;
}

/**
 * createHtml() 生成预览HTML文件
 * @param  array  $array 接收表单配置文件
 * @param  string $title 标题信息
 * @param  string $path  文件地址
 * @return string 返回 字符串
 */
function createHtml($array, $title)
{
    $strHtml = '';
    if ($array) {
        foreach ($array as $key => $value) {
            $html = "\t\t\t{\"title\": \"{$value['title']}\", \"data\": \"{$key}\", \"sName\": \"{$key}\", ";

            // 编辑
            if ($value['edit'] == 1) $html .= "\"edit\": {\"type\": \"{$value['type']}\", " . trim($value['options'], ',') . "}, ";

            // 搜索
            if ($value['search'] == 1) {
                $html .= "\"search\": {\"type\": \"text\"}, ";
            }

            // 排序
            if ($value['bSortable'] == 0) $html .= '"bSortable": false, ';

            // 回调
            if (!empty($value['createdCell'])) $html .= '"createdCell" : '.$value['createdCell'].', ';

            $strHtml .= trim($html, ', ')."}, \n";
        }
    }

$strHtml =  <<<html
    <!-- 表格按钮 -->
    <p id="me-table-buttons"></p>
    <!-- 表格数据 -->
    <table class="table table-striped table-bordered table-hover" id="show-table"></table>
    
    <script type="text/javascript">
        var m = mt({
            title: "{$title}",
            table: {
                "aoColumns": [
                    {$strHtml}
                ]       
            }
        });
        
        /**
        m.fn.extend({
            // 显示的前置和后置操作
            beforeShow: function(data, child) {
                return true;
            },
            afterShow: function(data, child) {
                return true;
            },
            
            // 编辑的前置和后置操作
            beforeSave: function(data, child) {
                return true;
            },
            afterSave: function(data, child) {
                return true;
            }
        });
        */
    
         \$(function(){
             m.init();
         });
    </script>
html;

    return $strHtml;
}


// 判断操作类型
if (isset($_GET) && isset($_GET['action'])) {
    $arrReturn = [
        'errCode' => 1,
        'errMsg' => '请求参数为空',
        'data' => null,
    ];

    switch ($_GET['action']) {
        case 'table':
            include './includes/PdoObject.php';
            if (isset($_POST) && $_POST && isset($_POST['table']) && !empty($_POST['table'])) {
                $strTable = trim($_POST['table']);
                unset($_POST['table']);
                $params = array_merge($arrConfig, $_POST);
                $mysql = PdoObject::getInstance($params);
                $pdoStatement = $mysql->query('SHOW TABLES');
                $array = $pdoStatement->fetchAll(PDO::FETCH_NUM);
                $arrReturn['errMsg'] = '数据表不存在';
                if ($array) {
                    $isHave = false;
                    foreach ($array as $value) {
                        if (in_array($strTable, $value)) {
                            $isHave = true;
                            break;
                        }
                    }

                    if ($isHave) {
                        $pdoStatement = $mysql->query('SHOW FULL COLUMNS FROM `'.$strTable.'`');
                        $array = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
                        $arrReturn['errCode'] = 0;
                        $arrReturn['errMsg'] = 'success';
                        $arrReturn['data'] = createForm($array);
                    }
                }
            }

            break;
        case 'edit':
            if (isset($_POST) && $_POST && isset($_POST['title']) && !empty($_POST['title'])) {
                $arrReturn = [
                    'errCode' => 0,
                    'errMsg' => 'success',
                    'data' => highlight_string(createHtml($_POST['attr'], $_POST['title']), true),
                ];
            }

            break;
    }

    header('application/json; charset=utf-8');
    exit(json_encode($arrReturn, 320));
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
        div.main {margin-top:70px;}
        .mt20 {margin-top:20px}
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
            <a class="navbar-brand" href="./index.php"> meTables -- 使用示例 </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav pull-right">
                <li><a href="./index.php">首页</a></li>
                <li class="active"><a href="./create.php">创建视图文件</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container theme-showcase main" role="main">
    <div class="row">
        <div class="col-md-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#db-div" aria-controls="db-div" role="tab" data-toggle="tab">数据库配置</a>
                </li>
                <li role="presentation">
                    <a href="#view-edit" aria-controls="view-edit" role="tab" data-toggle="tab" id="tab-view-edit">视图编辑</a>
                </li>
                <li role="presentation">
                    <a href="#view" aria-controls="view" role="tab" data-toggle="tab" id="tab-view">生成视图文件</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="db-div">
                    <div class="col-md-12 mt20">
                        <form action="./create.php?action=table" method="post" id="db-form">
                            <div class="form-group">
                                <label for="db-user">数据库用户名</label>
                                <input type="text" required="true" rangelength="[2, 20]" name="user" class="form-control" value="<?=$arrConfig['user']?>" id="db-user" placeholder="用户名">
                            </div>
                            <div class="form-group">
                                <label for="db-password">数据库密码</label>
                                <input type="password" required="true" rangelength="[2, 20]" name="password" class="form-control" value="<?=$arrConfig['password']?>" id="db-password" placeholder="密码">
                            </div>
                            <div class="form-group">
                                <label for="db-name">数据库名称</label>
                                <input type="text" required="true" rangelength="[2, 20]" name="dbname" class="form-control" value="<?=$arrConfig['dbname']?>" id="db-name" placeholder="数据库名称">
                            </div>
                            <div class="form-group">
                                <label for="db-table">数据表名称</label>
                                <input type="text" required="true" rangelength="[2, 20]" name="table" class="form-control" id="db-table" placeholder="数据表名称">
                            </div>
                            <button type="submit" class="btn btn-success">提交</button>
                        </form>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="view-edit">
                    <div class="col-md-12 mt20">
                        <form action="./create.php?action=edit" method="POST" id="edit-form">
                        </form>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="view">
                    <div class="col-md-12 mt20">
                        <div class="code" id="view-code"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="./public/js/jquery.min.js"></script>
<script src="./public/js/bootstrap.min.js"></script>
<script src="./public/js/jquery.dataTables.min.js"></script>
<script src="./public/js/jquery.dataTables.bootstrap.js"></script>
<script src="./public/js/jquery.validate.min.js"></script>
<script src="./public/js/meTables.js"></script>
<script src="./public/js/layer/layer.js"></script>
<script type="text/javascript">
    $(function(){
        // 查询数据库
        $("#db-form").submit(function(evt){
            var $fm = $(this);
            evt.preventDefault();
            if ($(this).validate().form()) {
                mt.ajax({
                    url: $fm.prop("action"),
                    data: $fm.serialize(),
                    type: $fm.prop("method"),
                    dataType: "json"
                }).done(function(json){
                    if (json.errCode === 0) {
                        $("#edit-form").html(json.data + '<div class="form-group">\
                        <label for="db-name">标题</label>\
                            <input type="text" required="true" rangelength="[2, 20]" name="title" class="form-control"  placeholder="标题">\
                            </div><button type="submit" class="btn btn-success">提交</button>');
                        $("#tab-view-edit").trigger("click");
                    } else {
                        layer.msg(json.errMsg, {icon:5})
                    }
                });
            };
        });

        // 生成试图文件
        $("#edit-form").submit(function(evt){
            var $fm = $(this);
            evt.preventDefault();
            mt.ajax({
                url: $fm.prop("action"),
                data: $fm.serialize(),
                type: $fm.prop("method"),
                dataType: "json"
            }).done(function(json){
                if (json.errCode === 0) {
                    $("#view-code").html(json.data);
                    $("#tab-view").trigger("click");
                } else {
                    layer.msg(json.errMsg, {icon:5})
                }
            });
        });
    });
</script>
</body>
</html>
