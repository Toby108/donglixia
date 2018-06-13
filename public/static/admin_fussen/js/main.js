/**
 * Created by jiuSuo on 2018/3/1.
 */

/**
 *设置页面URL参数
 * @param url  传入的地址
 * @param arg  参数的参数名
 * @param arg_val 参数值
 * @returns {string} URL链接
 */
function setUrl(url,arg,arg_val)
{
    var pattern=arg+'=([^&]*)';
    var replaceText=arg+'='+arg_val;
    var result = "";
    if(url.match(pattern))
    {
        var tmp='/('+ arg+'=)([^&]*)/gi';
        tmp = url.replace(eval(tmp),replaceText);
        result = tmp;
    }
    else
    {
        if(url.match('[\?]'))
        {
            result =  url+'&'+replaceText;
        }
        else
        {
            result =  url+'?'+replaceText;
        }
    }
    return result;
}

/**
 * 将form中的值转换为键值对
 * @param form
 * @returns {{}}
 */
function getFormJson(form) {
    var res = {};
    var arr = $(form).serializeArray();
    $.each(arr, function () {
        if (res[this.name] !== undefined) {
            if (!res[this.name].push) {
                res[this.name] = [res[this.name]];
            }
            res[this.name].push(this.value || '');
        } else {
            res[this.name] = this.value || '';
        }
    });

    return res;
}

/**
 * 重新加载数据表格
 * @param form 表单id
 * @param curr 当前页码
 */
function reloadTable(form, curr) {
    if (!curr) {curr = 1;}

    var where = {};
    var arr = $(form).serializeArray();
    $.each(arr, function () {
        where[this.name] = this.value || '';
    });
    history.pushState({}, '', window.location.origin+window.location.pathname);
    tableIns.reload({
        where: where
        ,page:{
            curr: curr //重新从第 curr 页开始
        }
    });

}

//隐藏/显示侧边栏
$('.nav-display').on('click', function () {
    var sideWidth = $('.layui-side').width();
    $('.tpl-icon-rotate').removeClass('tpl-icon-rotate');
    if (sideWidth === 200) {
        $('.layui-body,.layui-layout-left').animate({left: '0px'});
        $('.layui-side,.layui-layout-admin .layui-logo').animate({width: '0px'});
        $('.nav-display .layui-icon').removeClass('layui-icon-shrink-right').addClass('layui-icon-spread-left');
    } else {
        $('.layui-body,.layui-layout-left').animate({left: '200px'});
        $('.layui-side,.layui-layout-admin .layui-logo').animate({width: '200px'});
        $('.nav-display .layui-icon').removeClass('layui-icon-spread-left').addClass('layui-icon-shrink-right');
    }
});

/*弹窗显示修改密码*/
function changePwd(uid, title) {
    layui.use('layer', function () {
        $('#pwdFormCommon').find('input[name="uid"]').val(uid);
        var modal = layer.open({
            type: 1
            , title: title
            , btn: ['确定', '取消']
            , content: $('#pwdModalCommon').html()
            , yes: function (index, element) {
                $.post('/admin_layui/user/changePassword.html', $(element).find('form').serialize(), function (result) {
                    if (result.code) {
                        layer.close(modal);
                        layer.msg(result.msg, {time: 2000});
                    } else {
                        layer.alert(result.msg, {icon: 2, title: '保存失败！'});
                    }
                }, 'json');
            }
        });
    });
}



