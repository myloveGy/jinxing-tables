meTables.js 基于 jquery.dataTables.js 表格
===================================

### 简介

因为jquery.dataTables.js 只是显示数据，没有自带的编辑、新增、删除数据的功能，需要依靠扩展实现，所以自己写了一个编辑、新增、删除数据的功能

### 依赖声明
* jQuery v2.1.1 
* Bootstrap v3.2.0
* DataTables 1.10.10
* layer-v2.1
* jQuery Validation Plugin - v1.14.0

### 简单使用
#### html
```html
<!-- 按钮信息 -->
<p id="me-table-buttons"></p>

<!-- 表格数据 -->
<table class="table table-striped table-bordered table-hover" id="show-table"></table>

<script>
/**
 * 简单配置说明
 * title 配置表格名称
 * table DataTables 的配置 
 * --- aoColumns 中的 value, search, edit, defaultOrder, isHide 是 meTables 的配置
 * ------ value 为编辑表单radio、select, checkbox， 搜索的表单的select 提供数据源,格式为一个对象 {"值": "显示信息"}
 * ------ search 搜索表单配置(不配置不会生成查询表单), type 类型支持 text, select 其他可以自行扩展
 * ------ edit 编辑表单配置（不配置不会生成编辑表单）, 
 * --------- type 类型支持hidden, text, password, file, radio, select, checkbox, textarea 等等 
 * --------- meTable.inputCreate 等后缀函数为其生成表单元素，可以自行扩展
 * --------- 除了表单元素自带属性，比如 required: true, number: true 等为 jquery.validate.js 的验证配置
 * --------- 最终生成表单元素 <input name="name" required="true" number="true" />
 * ------ defaultOrder 设置默认排序的方式(有"ace", "desc")
 * ------ isHide 该列是否需要隐藏 true 隐藏
 * 其他配置查看 meTables 配置
 */
var m = meTables({
    title: "地址信息",
    table: {
        "aoColumns":[
            {"title": "id", "data": "id", "sName": "id",  "defaultOrder": "desc",
                "edit": {"type": "text", "required":true,"number":true}
            },
            {"title": "地址名称", "data": "name", "sName": "name",
                "edit": {"type": "text", "required": true, "rangelength":"[2, 40]"},
                "search": {"type": "text"},
                "bSortable": false
            },
            {"title": "父类ID", "data": "pid", "sName": "pid", "value": {"0": "中国", "1": "上海"},
                "edit": {"type": "text", "required": true, "number": true},
                "search": {"type":"select"}
            }
        ]
    }
});

$(window).ready(function(){
    m.init();
});
</script>
```

#### 生成视图
![试图文件](./public/images/desc5.png)

#### 关于搜索条件和排序字段的处理

搜索表单的查询信息以及排序条件都会拼接到dataTables 提交数据中

```js

    meTables.fnServerData = function(sSource, aoData, fnCallback) {
        var attributes = aoData[2].value.split(","),
            mSort 	   = (attributes.length + 1) * 5 + 2;

        // 添加查询条件
        var data = $(meTables.fn.options.searchForm).serializeArray();
        for (i in data) {
            if (!meTables.empty(data[i]["value"]) && data[i]["value"] != "All") {
                aoData.push({"name": "params[" + data[i]['name'] + "]", "value": data[i]["value"]});
            }
        }

        // 添加排序字段信息
        meTables.fn.push(aoData, {"orderBy": attributes[parseInt(aoData[mSort].value)]}, "params");

        // 添加其他字段信息
        meTables.fn.push(aoData, meTables.fn.options.params, "params");

        // ajax请求
        meTables.ajax({
            url: sSource,
            data: aoData,
            type: meTables.fn.options.sMethod,
            dataType: 'json'
        }).done(function(data){
            if (data.errCode != 0) {
                return layer.msg(meTables.fn.getLanguage("sAppearError") + data.errMsg, {
                    time:2000,
                    icon:5
                });
            }

            fnCallback(data.data);
        });
    };

```

#### 服务器数据的处理(PHP代码)

```php
// 默认使用的POST提交的数据

// 请求次数
$intEcho = isset($_POST['sEcho']) ? (int)$_POST['sEcho'] : 0;

// 查询参数(查询条件排序字段)
$params = isset($_POST['params']) ? $_POST['params'] : [];

// 查询开始位置(分页启始位置)
$intStart = isset($_POST['iDisplayStart']) ? (int)$_POST['iDisplayStart'] : 0; 

// 查询数据条数
$intLength = isset($_POST['iDisplayLength']) ? (int)$_POST['iDisplayLength'] : 10;

// 排序的方式
$sort = isset($_POST['sSortDir_0']) ? trim($_POST['sSortDir_0']) : 'desc';

// 处理排序字段
if (isset($params['orderBy']) && !empty($params['orderBy'])) {
    $field = trim($params['orderBy']);
    unset($params['orderBy']);
} else {
    $field = 'id';
}

// 处理查询条件
if (!empty($params)) {

    /**
     * 这里的$params 其实就是前台搜索表单中的数据(查询字段对应值的一个数组)
     * ['id' => '1', 'name' => '湖南']
     */
     
    $arrWhere = $bindParams = [];
    foreach ($params as $key => $value) {
        // 具体对应查询条件根据实际情况处理，我这里使用最简单的处理方式('=')
        $arrWhere[] = '`'.$key.'` = ?';
        $bindParams[] = trim($value);
    }
     
    $where = ' WHERE '.implode(' AND ', $arrWhere);
} else {
    $where = '';
    $bindParams = [];
}

// 实例化PDO类
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=test;charset=utf8', 'user', 'password');

$table = 'china';

// 查询数据总条数
$intTotal = 0;
$strCount = 'SELECT COUNT(*) AS `total` FROM `' . $table . '` ' . $where;
$stem = $pdo->prepare($strCount);
if ($stem->execute($bindParams)) {
    $array = $stem->fetch(PDO::FETCH_ASSOC);
    $intTotal = (int)$array['total'];
}

// 查询具体数据
if ($intTotal > 0) {
    $strSql = 'SELECT * FROM `' . $table . '` ' . $where . ' ORDER BY `' . $field . '` ' . $sort . ' LIMIT '.$intStart.','.$intLength;
    $stem = $pdo->prepare($strSql);
    if ($stem->execute($bindParams)) {
        $data = $stem->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $data = [];
}

// 返回json数据
header('application/json; charset=utf-8');
exit(json_encode([
    'errCode' => 0,
    'errMsg' => 'success',
    'data' => [
        'sEcho' => $intEcho,  					// 请求次数
        'iTotalRecords' => count($data),        // 当前页条数
        'iTotalDisplayRecords' => $intTotal,  	// 数据总条数
        'aaData' => $data,						// 数据信息
    ]  
], 320));

```

#### 服务器返回数据说明
* 查询返回json
```
{
  errCode: 0, 
  errMsg: "操作成功", 
  data: {
    sEcho: 1,                   // 查询次数
    iTotalRecords: 10,          // 当页数据条数
    iTotalDisplayRecords: 100,  // 数据总条数 
    aaData: [                   // 数据
      {id: 1, name: "中国"},
      {id: 2, name: "上海"}
    ]
  }
}
```
* 新增、修改、删除返回json
1. 处理成功
```
{
    errCode: 0,
    errMsg: "操作成功",
    data: {
        id: 1,
        name: "中国"
    }
}
```
2. 处理失败
```js
{
  errCode: 1,
  errMsg: "处理失败",
  data: null
}
```
### 配置说明

* 在 meTables() 函数中传递的对象信息会覆盖如下的默认配置信息
* 选择器配置一定要使用ID选择器，例如：sModal, sTable, sFormId, searchForm, buttonSelector
```js
var config = {
    title: "",                  // 表格的标题  
    language: "zh-cn",          // 使用语言          
    pk: "id",		            // 行内编辑、多选删除、多选编辑 pk索引值
    sModal: "#table-modal",     // 编辑Modal选择器
    sTable:  "#show-table", 	// 显示表格选择器
    sFormId: "#edit-form",		// 编辑表单选择器
    sMethod: "POST",			// 查询数据的请求方式
    bCheckbox: true,			// 需要多选框
    params: null,				// 请求携带参数
    ajaxRequest: false,         // ajax一次性获取数据
    
    // 关于地址配置信息
    urlPrefix: "", 
    urlSuffix: "",
    url: {
        search: "search",
        create: "create",
        update: "update",
        delete: "delete",
        export: "export",
        upload: "upload",
        editable: "editable",
        deleteAll: "delete-all"
    },
    
    // 最终生成地址 urlPrefix + url.search + urlSuffix;
             
    // dataTables 表格默认配置对象信息
    table: {
        // "fnServerData": fnServerData,		// 获取数据的处理函数
        // "sAjaxSource":      "search",		// 获取数据地址
        "bLengthChange": true, 			// 是否可以调整分页
        "bAutoWidth": false,           	// 是否自动计算列宽
        "bPaginate": true,			    // 是否使用分页
        "iDisplayStart":  0,
        "iDisplayLength": 10,
        "bServerSide": true,		 	// 是否开启从服务器端获取数据
        "bRetrieve": true,
        "bDestroy": true,
        // "processing": true,		    // 是否使用加载进度条
        // "searching": false,
        "sPaginationType":  "full_numbers"     // 分页样式
        // "order": [[1, "desc"]]       // 默认排序，
        // sDom: "t<'row'<'col-xs-6'li><'col-xs-6'p>>"
    },

    // 关于搜索的配置
    searchHtml: "",				// 搜索信息额外HTML
    searchType: "middle",		// 搜索表单位置
    searchForm: "#search-form",	// 搜索表单选择器

    // 搜索信息(只对searchType !== "middle") 情况
    search: {
        render: true,           // 是否渲染表格，自己创建了搜索表单，可以将该值设置为false
        type: "append",         // 渲染时添加表单html的jquery 函数方式
        // 搜索按钮
        button: {
            "class": "btn btn-info btn-sm", // 搜索按钮class
            "icon": "ace-icon fa fa-search" // 搜索按钮的icon
        }
    },

    // 上传文件选择器， 依赖ace.min.js 中的 ace_file_input() 函数
    fileSelector: [],			

    // 编辑表单信息
    form: {
        "method": "post", 
        "class":  "form-horizontal",
        "name":   "edit-form"
    },

    // 编辑表单验证方式（jquery.validate 需要的验证对象信息）
    formValidate: {
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: false,
        highlight: function (e) {
            $(e).closest('.form-group').removeClass('has-info').addClass('has-error');
        },
        success: function (e) {
            $(e).closest('.form-group').removeClass('has-error');//.addClass('has-info');
            $(e).remove();
        }
    },

    // 表单编辑其他信息
    editFormParams: {				// 编辑表单配置
        bMultiCols: false,          // 是否多列
        iColsLength: 1,             // 几列
        aCols: [3, 9],              // label 和 input 栅格化设置
        sModalClass: "",			// 弹出模块框配置
        sModalDialogClass: ""		// 弹出模块的class
    },

    // 关于详情的配置
    bViewFull: false, // 详情打开的方式 1 2 打开全屏
    // layer.open() 函数需要的配置信息
    oViewConfig: {
        type: 1,
        shade: 0.3,
        shadeClose: true,
        maxmin: true,
        area: ['50%', 'auto']
    },

    detailTable: {                   // 查看详情配置信息
        bMultiCols: false,
        iColsLength: 1
    },

    // 子表格配置信息
    bChildTables: false, // 是否开启
    childTables: {
        sTable: "#child-table",
        sModal: "#child-modal",
        sFormId: "#child-form",
        urlPrefix: "",
        urlSuffix: "",
        url: {
            "search": "view",  // 查询
            "create": "create", // 创建
            "update": "update",	// 修改
            "delete": "delete" // 删除
        },
        sClickSelect: "td.child-control",
        table: 	{
            "bPaginate": false,             // 不使用分页
            "bLengthChange": false,         // 是否可以调整分页
            "bServerSide": true,		 	// 是否开启从服务器端获取数据
            "bAutoWidth": false,
            "searching": false,				// 搜索
            "ordering": false			 	// 排序
        },

        detailTable: {                   // 查询详情配置信息
            bMultiCols: false,
            iColsLength: 1
        },

        editFormParams: {				// 编辑表单配置
            bMultiCols: false,          // 是否多列
            iColsLength: 1,             // 几列
            aCols: [3, 9],              // label 和 input 栅格化设置
            sModalClass: "",			// 弹出模块框配置
            sModalDialogClass: ""		// 弹出模块的class
        }
    },

    // 开启行处理
    editable: null,

    // 默认按钮信息
    buttonHtml: "",
    // 按钮添加容器
    buttonSelector: "#me-table-buttons",
    // 按钮添加方式
    buttonType: "append",
    // 默认按钮信息
    buttons: {
        // 添加数据
        create: {
            bShow: true, // 是否显示出来
            icon: "ace-icon fa fa-plus-circle blue",
            className: "btn btn-white btn-primary btn-bold"
        },
        
        // 多选编辑
        updateAll: {
            bShow: true,
            icon: "ace-icon fa fa-pencil-square-o orange",
            className: "btn btn-white btn-info btn-bold"
        },
        
        // 多选删除
        deleteAll: {
            bShow: true,
            icon: "ace-icon fa fa-trash-o red",
            className: "btn btn-white btn-danger btn-bold"
        },
        
        // 刷新表格
        refresh: {
            bShow: true,
            icon: "ace-icon fa  fa-refresh",
            className: "btn btn-white btn-success btn-bold"
        },
        
        // 导出数据
        export: {
            bShow: true,
            icon: "ace-icon glyphicon glyphicon-export",
            className: "btn btn-white btn-warning btn-bold"
        }
    }

    // 操作选项
    ,operations: {
        isOpen: true, // 是否显示
        width: "120px",
        defaultContent: "",
        buttons: {
            "see": {"className": "btn-success", "cClass":"me-table-detail",  "icon":"fa-search-plus",  "sClass":"blue"},
            "update": {"className": "btn-info", "cClass":"me-table-update", "icon":"fa-pencil-square-o",  "sClass":"green"},
            "delete": {"className": "btn-danger", "cClass":"me-table-delete", "icon":"fa-trash-o",  "sClass":"red"}
        }
    }
};

```