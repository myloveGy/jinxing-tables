<?php

session_start();

include './includes/data.php';
include './includes/functions.php';
include './includes/PdoObject.php';

/**
 * createForm() 生成表格配置表单信息
 * @access private
 *
 * @param  array $array 数据表信息
 *
 * @return  string 返回HTML
 */
function createForm($array)
{
    $strHtml = '<table class="table table-striped table-bordered table-hover" id="show-table">
    <thead>
        <tr>
            <th width="150px">字段</th>
            <th width="150px">标题</th>
            <th>编辑</th>
            <th width="80px">搜索</th>
            <th width="80px">排序</th>
            <th>回调</th>    
        </tr>
    </thead><tbody>';
    foreach ($array as $value) {
        $key     = $value['Field'];
        $sTitle  = isset($value['Comment']) && !empty($value['Comment']) ? $value['Comment'] : $value['Field'];
        $sOption = isset($value['Null']) && $value['Null'] == 'NO' ? '"required": true,' : '';
        if (stripos($value['Type'], 'int(') !== false) $sOption .= '"number": true,';
        if (stripos($value['Type'], 'varchar(') !== false) {
            $sLen    = trim(str_replace('varchar(', '', $value['Type']), ')');
            $sOption .= '"rangelength": "[2, ' . $sLen . ']"';
        }

        $sOther = stripos($value['Field'], '_at') !== false ? 'MeTables.dateTimeString' : '';

        $strHtml .= <<<HTML
<tr>
    <td>
        <input type="text" name="attr[{$key}][data]" class="form-control" value="{$sTitle}" required="true"/>
    </td>
    <td>
        <input type="text" name="attr[{$key}][title]" class="form-control" value="{$sTitle}" required="true"/>
    </td>
    <td>
        <select name="attr[{$key}][type]" class="form-control pull-left" style="width: 80px">
            <option value="" selected="selected">选择编辑类型</option>
            <option value="text" >text</option>
            <option value="hidden">hidden</option>
            <option value="select">select</option>
            <option value="radio">radio</option>
            <option value="password">password</option>
            <option value="textarea">textarea</option>
        </select>
        <input type="text" name="attr[{$key}][options]" class="form-control pull-left" value='{$sOption}'/>
    </td>
    <td>
        <select name="attr[{$key}][search]" class="form-control">
            <option value="1">开启</option>
            <option value="0" selected="selected">关闭</option>
        </select>
    </td>
    <td>
        <select name="attr[{$key}][bSortable]" class="form-control">
            <option value="1" >开启</option>
            <option value="0" selected="selected">关闭</option>
        </select>
    </td>
    <td><input type="text" name="attr[{$key}][createdCell]" class="form-control" value="{$sOther}" /></td>
</tr>
HTML;
    }

    return $strHtml . '</tbody></table>';
}

/**
 * createHtml() 生成预览HTML文件
 *
 * @param  array  $array 接收表单配置文件
 * @param  string $title 标题信息
 *
 * @return string 返回 字符串
 */
function createHtml($array, $title)
{
    $strHtml = '';
    if ($array) {
        foreach ($array as $key => $value) {
            $html = "\t\t\t{\"title\": \"{$value['title']}\", \"data\": \"{$key}\", ";

            // 编辑
            if ($value['edit'] == 1) $html .= "\"edit\": {\"type\": \"{$value['type']}\", " . trim($value['options'], ',') . "}, ";

            // 搜索
            if ($value['search'] == 1) {
                $html .= "\"search\": {\"type\": \"text\"}, ";
            }

            // 排序
            if ($value['bSortable'] == 0) $html .= '"bSortable": false, ';

            // 回调
            if (!empty($value['createdCell'])) $html .= '"createdCell" : ' . $value['createdCell'] . ', ';

            $strHtml .= trim($html, ', ') . "}, \n";
        }

        $strHtml = trim($strHtml, ", \n");
    }

    $strHtml = <<<html
    <!-- 表格按钮 -->
    <p id="me-table-buttons"></p>
    <!-- 表格数据 -->
    <table class="table table-striped table-bordered table-hover" id="show-table"></table>
    <script type="text/javascript">
        var m = $("#show-table").MeTables({
            title: "{$title}",
            table: {
                "columns": [
                    {$strHtml}
                ]       
            }
        });
        
       
        
        /**
        $.extend(m, {
            // 显示的前置和后置操作,需要暂停操作，返回false
            beforeShow: function(data, child) {
                
            },
            afterShow: function(data, child) {
                
            },
            
            // 编辑的前置和后置操作，需要暂停操作，返回false
            beforeSave: function(data, child) {
                
            },
            afterSave: function(data, child) {
                
            }
        });
        */
    </script>
html;

    return $strHtml;
}

// 判断操作类型
if ($action = get('action')) {
    switch ($_GET['action']) {
        case 'table':
            // 验证参数
            if ((!$table = trim(post('table'))) || empty(post('dns')) || empty(post('username'))) {
                error(401, '请求参数问题');
            }

            // 连接数据
            $mysql = PdoObject::getInstance([
                'dns'      => post('dns'),
                'username' => post('username'),
                'password' => post('password'),
            ]);

            // 查询表是否存在
            $pdoStatement = $mysql->getPdo()->query('SHOW TABLES like "' . $table . '"');
            if (!$array = $pdoStatement->fetchAll(PDO::FETCH_NUM)) {
                error(505, '查询失败');
            }

            // 查询表结构
            $pdoStatement = $mysql->getPdo()->query('SHOW FULL COLUMNS FROM `' . $table . '`');
            $array        = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            success(createForm($array));
            break;
        case 'edit':
            if (isset($_POST) && $_POST && isset($_POST['title']) && !empty($_POST['title'])) {
                $arrReturn = [
                    'errCode' => 0,
                    'errMsg'  => 'success',
                    'data'    => highlight_string(createHtml($_POST['attr'], $_POST['title']), true),
                ];
            }

            break;
    }
}