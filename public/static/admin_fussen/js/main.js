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
    // history.pushState({}, '', window.location.origin+window.location.pathname);//去除链接参数
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
function changePwd(id, title, url) {
    layui.use('layer', function () {
        $('#pwdFormCommon').find('input[name="user_id"]').val(id);
        var modal = layer.open({
            type: 1
            , title: title
            , btn: ['确定', '取消']
            , content: $('#pwdModalCommon').html()
            , yes: function (index, element) {
                $.post(url, $(element).find('form').serialize(), function (result) {
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

/*更改排序*/
function changeSort(id, type, url) {
    $.post(url, {id:id, type:type}, function (result) {
        if (result.code) {
            reloadTable('#searchForm');
        } else {
            layer.alert(result.msg, {icon:2});
        }
    }, 'json');
}

//ajax删除
function ajaxDelete(id, url) {
    layer.confirm('数据删除后无法恢复，确定继续吗？', function (index) {
        $.post(url, {id: id}, function (result) {
            layer.close(index);
            if (result.code) {
                reloadTable('#searchForm');
                layer.msg(result.msg);
            } else {
                layer.alert(result.msg, {icon: 2});
            }
        }, 'json');
    });
}

//选中数据，更改状态
function setState(ids, state, field_name, url) {
    if (!field_name) {field_name = 'state';}//状态字段名

    layer.confirm('确定更新状态吗？', function (index) {
        $.post(url, {id:ids, state:state, field_name:field_name}, function (result) {
            if (result.code) {
                reloadTable('#searchForm');//重新加载数据表格
                layer.msg(result.msg, {time: 1000});
            } else {
                layer.alert(result.msg, {icon: 2});
            }
        }, 'json');
    });
}