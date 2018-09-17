(function ($) {
    var MeTables = function (options) {
        this.options = $.extend(true, {}, MeTables.defaults, options);
        return this;
    };

    MeTables.defaults = {
        version: "1.0.0",
        author: {
            name: "liujinxing",
            email: "jinxing.liu@qq.com",
            github: ""
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

    $.fn.meTables = MeTables;

    $.fn.MeTables = function (opts) {
        return $(this).meTables(opts);
    };

    return $.fn.meTables
})(jQuery);


