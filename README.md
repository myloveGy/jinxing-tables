meTables.js 基于 jquery.dataTables.js 表格
===================================

### 简介

因为jquery.dataTables.js 只是显示数据，没有自带的编辑、新增、删除数据的功能，需要依靠扩展实现，所以自己写了一个编辑、新增、删除数据的功能

### 依赖声明
* jQuery v2.1.1 
* Bootstrap v3.2.0
* DataTables 1.10.15
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
 * --- columns 中的 value, search, edit, defaultOrder, hide 是 MeTables 的配置
 * ------ value 为编辑表单radio、select, checkbox， 搜索的表单的select 提供数据源,格式为一个对象 {"值": "显示信息"}
 * ------ search 搜索表单配置(不配置不会生成查询表单), type 类型支持 text, select 其他可以自行扩展
 * ------ edit 编辑表单配置（不配置不会生成编辑表单）, 
 * --------- type 类型支持hidden, text, password, file, radio, select, checkbox, textarea 等等 
 * --------- MeTable.inputCreate 等后缀函数为其生成表单元素，可以自行扩展
 * --------- 除了表单元素自带属性，比如 required: true, number: true 等为 jquery.validate.js 的验证配置
 * --------- 最终生成表单元素 <input name="name" required="true" number="true" />
 * ------ defaultOrder 设置默认排序的方式(有"ace", "desc")
 * ------ hide 该列是否需要隐藏 true 隐藏
 * 其他配置查看 MeTables 配置
 */

// 自定义表单处理方式
$.extend($.fn.meTables, {
    /**
     * 定义编辑表单(函数后缀名Create)
     * 使用配置 edit: {"type": "email", "id": "user-email"}
     * edit 里面配置的信息都通过 params 传递给函数
     */
    "emailCreate": function(params) {
        return '<input type="email" name="' + params.name + '"/>';
    },
    
    /**
     * 定义搜索表达(函数后缀名SearchCreate)
     * 使用配置 search: {"type": "email", "id": "search-email"}
     * search 里面配置的信息都通过 params 传递给函数
     */
    "emailSearchCreate": function(params) {
        return '<input type="text" name="' + params.name +'">';
    }
});


var m = $("#show-table").MeTables({
    title: "地址信息",
    table: {
        columns:[
            {
                title: "id", 
                data: "id", 
                defaultOrder: "desc",
                // type 类型默认为text,可以不写
                edit: {required: true, number: true}
            },
            {
                title: "地址名称", 
                data: "name", 
                edit: {required: true, rangelength: "[2, 40]"},
                search: {type: "text"},
                sortable: false
            },
            {
                title: "父类ID", 
                data: "pid", 
                value: {"0": "中国", "1": "上海"},
                edit: {required: true, number: true},
                search: {"type":"select"}
            }
        ]
    }
});

</script>
```

#### 生成视图
![试图文件](./public/images/desc5.png)

#### 关于搜索条件和排序字段的处理

搜索表单的查询信息以及排序条件都会拼接到dataTables 提交数据中

>请求参数说明(请求方式为get)

 名称     |  类型 | 说明
:--------|:------|:----
draw     | int   | 请求次数
offset   | int   | 分页偏移量(对应mysql 的 offset)
limit    | int   | 分页数据条数( 对应mysql 的 limit)
columns  | array | 表格的字段信息(data 为 null 忽略)
filters  | array | 查询的参数信息,定义了defaultFilters 数据也在里面
orderBy  | string| 排序条件(排序字段 排序方式： id asc)

#### 服务器数据的处理(PHP代码)

```php
// 默认使用get 请求参数

// 请求次数
$draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 0;

// 查询参数(查询条件排序字段)
$filters = isset($_GET['filters']) ? $_GET['filters'] : [];

// 查询开始位置(分页启始位置)
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0; 

// 查询数据条数
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// 排序的字段和方式
$orderBy = isset($_GET['orderBy']) ? trim($_GET['orderBy']) : 'id desc';

// 处理查询条件
if (!empty($filters)) {
    /**
     * 这里的 $filters 其实就是前台搜索表单中的数据(查询字段对应值的一个数组)
     * ['id' => '1', 'name' => '湖南']
     */
     
    $arrWhere = $bindParams = [];
    foreach ($filter as $key => $value) {
        // 具体对应查询条件根据实际情况处理，我这里使用最简单的处理方式('=')
        $arrWhere[]   = '`'.$key.'` = ?';
        $bindParams[] = trim($value);
    }
     
    $where = ' WHERE ' . implode(' AND ', $arrWhere);
} else {
    $where      = '';
    $bindParams = [];
}

// 实例化PDO类
$pdo   = new PDO('mysql:host=127.0.0.1;port=3306;dbname=test;charset=utf8', 'user', 'password');
$table = 'china';

// 查询数据总条数
$intTotal = 0;
$strCount = 'SELECT COUNT(*) AS `total` FROM `' . $table . '` ' . $where;
$stem     = $pdo->prepare($strCount);
if ($stem->execute($bindParams)) {
    $array    = $stem->fetch(PDO::FETCH_ASSOC);
    $intTotal = (int)$array['total'];
}

// 查询具体数据
if ($intTotal > 0) {
    $strSql = 'SELECT * FROM `' . $table . '` ' . $where . ' ORDER BY `' . $field . '` ' . $sort . ' LIMIT '.$intStart.','.$intLength;
    $stem   = $pdo->prepare($strSql);
    if ($stem->execute($bindParams)) {
        $data = $stem->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $data = [];
}

// 返回json数据
header('Content-Type: application/json; charset=utf-8');
exit(json_encode([
    'draw'            => 0,
    'data'            => [
        'id'   => 1,
        'name' => 'name'
    ],
    'recordsFiltered' => 99,
    'recordsTotal'    => 99  
], 320));

```

#### 服务器返回数据说明
* 查询返回 json
```
{
    draw: 1,                   // 查询次数
    recordsFiltered: 100,      // 当页数据条数
    recordsTotal: 100,        // 数据总条数 
    data: [                   // 数据
      {id: 1, name: "中国"},
      {id: 2, name: "上海"}
    ]
}
```
* 新增、修改、删除返回json
1. 处理成功
```
{
    code: 0,
    msg: "操作成功",
    data: {
        id: 1,
        name: "中国"
    }
}
```
2. 处理失败
```js
{
  code: 1,
  msg: "处理失败",
  data: null
}
```
### 配置说明

>目前配置选择器都只能使用ID选择器

| 配置名称 | 字段类型 | 默认值 | 说明 |
|----------------|---------|-------------------|---------|
| title          | string  | 空字符串           |  表格标题(会在编辑和新增弹框显示)      |
| pk             | string  | id                | 数据主键值(行内编辑,和多个删除需要使用) |
| modalSelector  | string  | #table-modal      | 弹出模块框的选择器                    |
| formSelector   | string  | #edit-form        | 编辑表单的选择器                      |
| defaultFilters | object  | null              | 默认查询条件(该配置在查询和导出都会提交给服务器)|
| filters        | string  | filters           | 查询条件提交给服务器字段名称                  |
|   

### 请求相关配置
| 配置名称 | 字段类型 | 默认值 | 说明 |
|----------------|---------|-------------------|---------|
| isSuccess      | function | function(response) { return response.code === 0 } | 验证请求是否成功(response 为响应json 数据)
| getMessage     | function | function(response) { return response.msg; }       | 获取响应的提示信息(response 为响应 json 数据)
| urlPrefix      | string   | 空字符串    | 路由前缀     |
| urlSuffix      | string   | 空字符串    | 路由后缀     |
| url            | object   |            | 具体路由信息  |
| url.search     | string   | search     | 搜索&列表显示数据请求地址 |
| url.create     | string   | create     | 新增数据请求地址         |
| url.update     | string   | update     | 编辑数据请求地址         |
| url.delete     | string   | delete     | 删除数据请求地址         |
| url.export     | string   | export     | 导出数据请求地址         |
| url.editable   | string   | editable   | 行内编辑请求地址         |
| url.deleteAll  | string   | delete-all | 多删除请求地址           |

 

* 在 meTables() 函数中传递的对象信息会覆盖如下的默认配置信息
* 选择器配置一定要使用ID选择器，例如：sModal, sTable, sFormId, searchForm, buttonSelector
```js
    var default_config = {

// 搜索相关
searchHtml: "",				    // 搜索信息额外HTML
searchType: "middle",		    // 搜索表单位置
searchForm: "#search-form",	    // 搜索表单选择器
searchInputEvent: "blur",       // 搜索表单input事件
searchSelectEvent: "change",    // 搜索表单select事件
// 搜索信息(只对searchType !== "middle") 情况
search: {
render: true,
type: "append",
button: {
"class": "btn btn-info btn-sm",
"icon": "ace-icon fa fa-search"
}
},

fileSelector: [],			// 上传文件选择器

// 编辑表单信息
form: {
"method": "post",
"class": "form-horizontal",
"name": "edit-form"
},

// 编辑表单验证方式
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
editFormParams: {			// 编辑表单配置
multiCols: false,       // 是否多列
colsLength: 1,          // 几列
cols: [3, 9],           // label 和 input 栅格化设置
modalClass: "",			// 弹出模块框配置
modalDialogClass: ""	// 弹出模块的class
},

// 关于详情的配置
viewFull: false, // 详情打开的方式 1 2 打开全屏
viewConfig: {
type: 1,
shade: 0.3,
shadeClose: true,
maxmin: true,
area: ['50%', 'auto']
},

detailTable: {                   // 查看详情配置信息
multiCols: false,
colsLength: 1
},

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

// dataTables 表格默认配置对象信息
table: {
paging: true,
lengthMenu: [10, 30, 50, 100],
searching: false,
ordering: true,
info: true,
autoWidth: false,
processing: true,
serverSide: true,
paginationType: "full_numbers",
language: $.getValue(MeTables.language, "dataTables"),
},

// 开启行处理
editable: null,
editableMode: "inline",

// 默认按钮信息
buttonHtml: "",
// 按钮添加容器
buttonSelector: "#me-table-buttons",
// 按钮添加方式
buttonType: "append",
// 默认按钮信息
buttons: {
create: {
icon: "ace-icon fa fa-plus-circle blue",
className: "btn btn-white btn-primary btn-bold"
},
updateAll: {
icon: "ace-icon fa fa-pencil-square-o orange",
className: "btn btn-white btn-info btn-bold"
},
deleteAll: {
icon: "ace-icon fa fa-trash-o red",
className: "btn btn-white btn-danger btn-bold"
},
refresh: {
func: "search",
icon: "ace-icon fa  fa-refresh",
className: "btn btn-white btn-success btn-bold"
},
export: {
icon: "ace-icon glyphicon glyphicon-export",
className: "btn btn-white btn-warning btn-bold"
}
}

// 需要序号
, number: {
title: $.getValue(MeTables.language, "meTables.number"),
data: null,
view: false,
render: function (data, type, row, meta) {
if (!meta || $.isEmptyObject(meta)) {
return false;
}

return meta.row + 1 + meta.settings._iDisplayStart;
},
sortable: false
}

// 需要多选框
, checkbox: {
data: null,
sortable: false,
class: "center text-center",
title: "<label class=\"position-relative\">" +
"<input type=\"checkbox\" class=\"ace\" /><span class=\"lbl\"></span></label>",
view: false,
createdCell: function (td, data, array, row) {
$(td).html('<label class="position-relative">' +
'<input type="checkbox" class="ace" data-row="' + row + '" />' +
'<span class="lbl"></span>' +
'</label>');
}
}
// 操作选项
, operations: {
width: "120px",
defaultContent: "",
title: $.getValue(MeTables.language, "meTables.operations"),
sortable: false,
data: null,
buttons: {
see: {
title: $.getValue(MeTables.language, "meTables.see"),
className: "btn-success",
operationClass: "me-table-detail",
icon: "fa-search-plus",
colorClass: "blue"
},
update: {
title: $.getValue(MeTables.language, "meTables.update"),
className: "btn-info",
operationClass: "me-table-update",
icon: "fa-pencil-square-o",
colorClass: "green"
},
delete: {
title: $.getValue(MeTables.language, "meTables.delete"),
className: "btn-danger",
operationClass: "me-table-delete",
icon: "fa-trash-o",
colorClass: "red"
}
}
}
}
;

```
