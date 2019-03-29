/**
 * Created by lipo on 2019/1/9 0009.
 */

/*在弹出窗口(全屏)中打开链接*/
function pop_window_full(title, url) {
    var index = layer.open({
        type: 2,
        title: title,
        content: url
    });
    layer.full(index);
}

/*在弹出窗口中打开链接()
 参数解释：
 title	标题
 url		请求的url
 id		需要操作的数据id
 w		弹出层宽度（缺省调默认值）
 h		弹出层高度（缺省调默认值）
 */
function pop_window(title, url, w, h) {
    layer_show(title, url, w, h);
}

/*
获取json对象的长度
 */
function getJsonLength(json) {
    var length = 0;
    for (var item in json) {
        length++;
    }
    return length;
}

!function($) {
    $.fn.Huitextarealength = function(options){
        var defaults = {
            minlength:0,
            maxlength:140,
            errorClass:"error",
            exceed:true,
        };
        var options = $.extend(defaults, options);
        this.each(function(){
            var that = $(this);
            var v = that.val();
            var l = v.length;
            var str = '<p class="textarea-numberbar"><em class="textarea-length">'+l+'</em>/'+options.maxlength+'</p>';
            that.parent().append(str);

            that.on("keyup",function(){
                v = that.val();
                l = v.length;
                if (l > options.maxlength) {
                    if(options.exceed){
                        that.addClass(options.errorClass);
                    }else{
                        v = v.substring(0, options.maxlength);
                        that.val(v);
                        that.removeClass(options.errorClass);
                    }
                }
                else if(l<options.minlength){
                    that.addClass(options.errorClass);
                }else{
                    that.removeClass(options.errorClass);
                }
                that.parent().find(".textarea-length").text(v.length);
            });

        });
    }
} (window.jQuery);