(function ($) {
    // 时间格式化
    Date.prototype.Format = function (fmt) {
        var o = {
            "M+": this.getMonth() + 1,
            "d+": this.getDate(),
            "h+": this.getHours(),
            "m+": this.getMinutes(),
            "s+": this.getSeconds(),
            "q+": Math.floor((this.getMonth() + 3) / 3),
            "S": this.getMilliseconds()
        };

        if (/(y+)/.test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
        }

        for (var k in o) {
            if (new RegExp("(" + k + ")").test(fmt)) {
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
            }
        }

        return fmt;
    };

    // 初始化处理
    var MeTables = function (options) {
        this.options = $.extend(true, {}, MeTables.defaults, options);
        return this;
    };

    // 语言配置
    meTables.language = {
        // 我的信息
        meTables: {
            "operations": "操作",
            "operationsSee": "查看",
            "operationsUpdate": "编辑",
            "operationsDelete": "删除",
            "btnCancel": "取消",
            "btnSubmit": "确定",
            "selectAll": "选择全部",
            "info": "详情",
            "insert": "新增",
            "update": "编辑",
            "exporting": "数据正在导出, 请稍候...",
            "appearError": "出现错误",
            "serverError": "服务器繁忙,请稍候再试...",
            "determine": "确定",
            "cancel": "取消",
            "confirm": "您确定需要删除这_LENGTH_条数据吗?",
            "confirmOperation": "确认操作",
            "cancelOperation": "您取消了删除操作!",
            "noSelect": "没有选择需要操作的数据",
            "operationError": "操作有误",
            "search": "搜索",
            "create": "添加",
            "updateAll": "修改",
            "deleteAll": "删除",
            "refresh": "刷新",
            "export": "导出",
            "pleaseInput": "请输入",
            "all": "全部"
        },

        // dataTables 表格
        dataTables: {
            "decimal": "",
            "emptyTable": "没有数据呢 ^.^",
            "info": "显示 _START_ 到 _END_ 共有 _TOTAL_ 条数据",
            "infoEmpty": "无记录",
            "infoFiltered": "(从 _MAX_ 条记录过滤)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "每页 _MENU_ 条记录",
            "loadingRecords": "加载中...",
            "processing": "处理中...",
            "search": "搜索:",
            "zeroRecords": "没有找到记录",
            "paginate": {
                "first": "首页",
                "last": "尾页",
                "next": "下一页",
                "previous": "上一页"
            },
            "aria": {
                "sortAscending": ": 正在进行正序排序",
                "sortDescending": ": 正在进行倒序排序"
            }
        }
    };

    //  默认配置信息
    MeTables.defaults = {
        title: "",                  // 表格的标题
        pk: "id",		            // 行内编辑pk索引值
        modalId: "#table-modal",     // 编辑Modal选择器
        tableId: "#show-table", 	// 显示表格选择器
        formId: "#edit-form",		// 编辑表单选择器
        method: "POST",			// 查询数据的请求方式
        checkbox: true,			// 需要多选框
        checkboxWidth: "auto",      // 设置宽度
        params: null,				// 请求携带参数
        ajaxRequest: false,         // ajax一次性获取数据
        searchHtml: "",				// 搜索信息额外HTML
        searchType: "middle",		// 搜索表单位置
        searchForm: "#search-form",	// 搜索表单选择器
        event: true,               // 是否监听事件
        searchInputEvent: "blur",   // 搜索表单input事件
        searchSelectEvent: "change",// 搜索表单select事件
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
        editFormParams: {				// 编辑表单配置
            bMultiCols: false,          // 是否多列
            iColsLength: 1,             // 几列
            aCols: [3, 9],              // label 和 input 栅格化设置
            sModalClass: "",			// 弹出模块框配置
            sModalDialogClass: ""		// 弹出模块的class
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
            bMultiCols: false,
            iColsLength: 1
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
            lengthMenu: [15, 30, 50, 100],
            searching: false,
            ordering: true,
            info: true,
            autoWidth: false,
            processing: true,
            serverSide: true,
            paginationType: "full_numbers"
        },

        // 子表格配置信息
        childTables: {
            tableId: "#child-table",
            modalId: "#child-modal",
            formId: "#child-form",
            urlPrefix: "",
            urlSuffix: "",
            url: {
                "search": "view",  // 查询
                "create": "create", // 创建
                "update": "update",	// 修改
                "delete": "delete" // 删除
            },
            clickSelect: "td.child-control",
            table: {
                paging: false,            // 不使用分页
                lengthChange: false,
                serverSide: true,
                autoWidth: false,
                searching: false,// 搜索
                ordering: false,// 排序
            },

            detailTable: {                   // 查询详情配置信息
                multiCols: false,
                colsLength: 1
            },

            editFormParams: {				// 编辑表单配置
                multiCols: false,          // 是否多列
                colsLength: 1,             // 几列
                cols: [3, 9],              // label 和 input 栅格化设置
                modalClass: "",			// 弹出模块框配置
                modalDialogClass: ""		// 弹出模块的class
            }
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
                show: true,
                icon: "ace-icon fa fa-plus-circle blue",
                className: "btn btn-white btn-primary btn-bold"
            },
            updateAll: {
                show: true,
                icon: "ace-icon fa fa-pencil-square-o orange",
                className: "btn btn-white btn-info btn-bold"
            },
            deleteAll: {
                show: true,
                icon: "ace-icon fa fa-trash-o red",
                className: "btn btn-white btn-danger btn-bold"
            },
            refresh: {
                show: true,
                icon: "ace-icon fa  fa-refresh",
                className: "btn btn-white btn-success btn-bold"
            },
            export: {
                show: true,
                icon: "ace-icon glyphicon glyphicon-export",
                className: "btn btn-white btn-warning btn-bold"
            }
        }

        // 操作选项
        , operations: {
            bOpen: true,
            width: "120px",
            defaultContent: "",
            buttons: {
                see: {
                    show: true,
                    className: "btn-success",
                    operationClass: "me-table-detail",
                    icon: "fa-search-plus",
                    colorClass: "blue"
                },
                update: {
                    show: true,
                    className: "btn-info",
                    operationClass: "me-table-update",
                    icon: "fa-pencil-square-o",
                    colorClass: "green"
                },
                delete: {
                    show: true,
                    className: "btn-danger",
                    operationClass: "me-table-delete",
                    icon: "fa-trash-o",
                    colorClass: "red"
                }
            }
        },
        version: "1.0.0",
        author: {
            name: "liujinxing",
            email: "jinxing.liu@qq.com",
            github: "https://github.com/myloveGy"
        }
    };

    // 获取数组信息
    $.getValue = function (arrValue, key, defaultValue) {
        if (key in arrValue) {
            return arrValue[key];
        }

        if (typeof key === "string") {
            var index = key.lastIndexOf(".");
            if (key.lastIndexOf(".") !== -1) {
                arrValue = $.getValue(arrValue, key.substr(0, index), defaultValue);
                key = key.substr(index + 1);
            }
        }

        return arrValue[key] ? arrValue[key] : defaultValue;
    };

    // 辅助函数
    $.fn.meTables = MeTables;

    $.fn.MeTables = function (opts) {
        return $(this).meTables(opts);
    };

    return $.fn.meTables
})(jQuery);


