<?php
// 设置头信息
header('Content-Type:text/html; charset=UTF-8');

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
    <style type="text/css">
        div.main {margin-top:70px;}
        p.bg-success {padding:10px;}
        .m-coll {margin-top:3px;}
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
        table: {
            "aoColumns":[
                {"title": "id", "data": "id", "sName": "id",  "defaultOrder": "desc",
                    "edit": {"type": "text", "required":true,"number":true}
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
