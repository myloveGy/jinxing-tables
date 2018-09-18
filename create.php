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
    foreach ($array as $k => $value) {
        $key    = $value['Field'];
        $title  = !empty($value['Comment']) ? $value['Comment'] : studly_case($key);
        $option = $value['Null'] == 'NO' ? 'required: true,' : '';
        if (stripos($value['Type'], 'int(') !== false) {
            $option       .= 'number: true,';
            $order_select = '<option value="1" selected="selected">开启</option>
            <option value="0" >关闭</option>';
        } else {
            $order_select = '<option value="1" >开启</option>
            <option value="0" selected="selected">关闭</option>';
        }

        if (stripos($value['Type'], 'varchar(') !== false) {
            $sLen   = trim(str_replace('varchar(', '', $value['Type']), ')');
            $option .= 'rangelength: "[2, ' . $sLen . ']"';
        }

        $other = stripos($key, '_at') !== false ? '$.fn.meTables.dateTimeString' : '';

        if (in_array($key, ['created_time', 'updated_time', 'created_at', 'updated_at'])) {
            $update_select = '';
            $order_select  = '<option value="1" selected="selected">开启</option>
            <option value="0" >关闭</option>';
        } elseif (get_value($value, 'Key') == 'PRI') {
            $update_select = '<input type="hidden" name="attr[' . $k . '][type]" value="hidden">
            <input type="hidden" name="primary_key" value="' . $key . '">';
        } else {
            $update_select = '<select name="attr[' . $k . '][type]" class="form-control pull-left" style="width: 80px">
            <option value="" selected="selected">选择编辑类型</option>
            <option value="text" >text</option>
            <option value="hidden">hidden</option>
            <option value="select">select</option>
            <option value="radio">radio</option>
            <option value="password">password</option>
            <option value="textarea">textarea</option>
        </select>
        <input type="text" name="attr[' . $k . '][options]" class="form-control pull-left" value=\'' . $option . '\'/>';
        }

        $strHtml .= <<<HTML
<tr>
    <td>
        <input type="text" name="attr[{$k}][data]" class="form-control" value="{$key}" required="true"/>
    </td>
    <td>
        <input type="text" name="attr[{$k}][title]" class="form-control" value="{$title}" required="true"/>
    </td>
    <td>
        {$update_select}
    </td>
    <td>
        <select name="attr[{$k}][search]" class="form-control">
            <option value="1">开启</option>
            <option value="0" selected="selected">关闭</option>
        </select>
    </td>
    <td>
        <select name="attr[{$k}][sortable]" class="form-control">
            {$order_select}
        </select>
    </td>
    <td><input type="text" name="attr[{$k}][createdCell]" class="form-control" value="{$other}" /></td>
</tr>
HTML;
    }

    return $strHtml . '</tbody></table>';
}

/**
 * createHtml() 生成预览HTML文件
 *
 * @param  array  $array       接收表单配置文件
 * @param  string $title       标题信息
 * @param  string $primary_key 主键
 *
 * @return string 返回 字符串
 */
function createHtml($array, $title, $primary_key)
{
    $strHtml = '';
    if ($array) {
        foreach ($array as $key => $value) {
            $columns = [];
            if ($title = get_value($value, 'title')) {
                $columns[] = 'title: "' . $title . '"';
            }

            if ($data = get_value($value, 'data')) {
                $columns[] = 'data: "' . $data . '"';
            }

            // 编辑
            if ($type = get_value($value, 'type')) {
                $edits = ['type: "' . $type . '"'];
                if ($options = trim(get_value($value, 'options'), ',')) {
                    $edits[] = $options;
                }

                $columns[] = 'edit: {' . implode(',', $edits) . '}';
            }

            // 搜索
            if (get_value($value, 'search') == 1) {
                $columns[] = 'search: {type: "text"}';
            }

            // 排序
            if (get_value($value, 'sortable') == 0) {
                $columns[] = 'sortable: false';
            }

            // 回调
            if (!empty($value['createdCell'])) {
                $columns[] = 'createdCell: ' . $value['createdCell'];
            }

            $strHtml .= "\t\t\t\t{\n \t\t\t\t\t" . implode(",\n \t\t\t\t\t", $columns) . "\n \t\t\t\t}, \n";
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
            pk: "{$primary_key}",
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
            if (!$title = post('title')) {
                error(404, '请输入标题');
            }
            success(highlight_string(createHtml(post('attr'), $title, post('primary_key')), true));
            break;
    }
}