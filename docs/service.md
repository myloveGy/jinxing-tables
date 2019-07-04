服务端处理说明
============

[TOC]

## 关于搜索条件和排序字段的处理

搜索表单的查询信息以及排序条件都会拼接到dataTables 提交数据中

>请求参数说明(请求方式为get)

| 名称     |  类型 | 说明|
|:--------|:------|:----|
|draw     | int   | 请求次数|
|offset   | int   | 分页偏移量(对应mysql 的 offset)|
|limit    | int   | 分页数据条数( 对应mysql 的 limit)|
|columns  | array | 表格的字段信息(data 为 null 忽略)|
|filters  | array | 查询的参数信息,定义了defaultFilters 数据也在里面|
|orderBy  | string| 排序条件(排序字段 排序方式： id asc)|

## 服务器数据的处理(PHP代码)

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

## 服务器返回数据说明

### 查询返回`json`

```json
{
    "draw": 1,                   
    "recordsFiltered": 100,      
    "recordsTotal": 100,         
    "data": [                   
      {"id": 1, "name": "中国"},
      {"id": 2, "name": "上海"}
    ]
}
```

字段说明

|字段名称|类型|说明信息|
|:------|:------|:----|
|`draw`|`int`|当次请求的唯一ID(jquery.datatables.js 会在请求中携带,接收返回即可)|
|`recordsFiltered`|`int`|当次请求数据条数|
|`recordsTotal`|`int`|数据总条数|
|`data`|`array`|表格数据信息|

### 新增、修改、删除返回`json`

#### 处理成功

```json
{
    "code": 0,
    "msg": "操作成功",
    "data": {
        "id": 1,
        "name": "中国"
    }
}
```

#### 处理失败

```json
{
    "code": 405,
    "msg": "处理失败",
    "data": null
}
```