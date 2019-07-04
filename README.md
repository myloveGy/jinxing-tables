meTables.js 基于 jquery.dataTables.js 表格
=========================================

### 简介

因为jquery.dataTables.js 只是显示数据，没有自带的编辑、新增、删除数据的功能，需要依靠扩展实现，所以自己写了一个编辑、新增、删除数据的功能

### 依赖声明
* jQuery v2.1.1 
* Bootstrap v3.2.0
* DataTables 1.10.15
* layer-v2.1
* jQuery Validation Plugin - v1.14.0

### 安装

```bash
bower install jinxing-tables
```

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
 * --- columns 中的 value, search, edit, defaultOrder, hide, view, export 是 MeTables 的配置
 * ------ value 为编辑表单radio、select, checkbox， 搜索的表单的select 提供数据源,格式为一个对象 {"值": "显示信息"}
 * ------ search 搜索表单配置(不配置不会生成查询表单), type 类型支持 text, select 其他可以自行扩展
 * ------ edit 编辑表单配置（不配置不会生成编辑表单）, 
 * --------- type 类型支持hidden, text, password, file, radio, select, checkbox, textarea 等等 
 * --------- meTables.inputCreate 等后缀函数为其生成表单元素，可以自行扩展
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
![试图文件](https://mylovegy.github.io/jinxing-tables/docs/images/data.png)

#### 后端返回

[后端返回请参考](https://mylovegy.github.io/jinxing-tables/?page=service)

### 使用文档

[使用文档请参考](https://mylovegy.github.io/jinxing-tables/?page=home) 

[配置说明请参考](https://mylovegy.github.io/jinxing-tables/?page=config)


