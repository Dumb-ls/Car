define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'comp/index' + location.search,
                    add_url: 'comp/add',
                    edit_url: 'comp/edit',
                    del_url: 'comp/del',
                    multi_url: 'comp/multi',
                    reset_url: 'comp/reset',
                    clear_url: 'comp/clear',
                    table: 'comp',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'compname', title: __('Compname')},
                        {field: 'compuser', title: __('Compuser')},
                        // {field: 'comppass', title: __('Comppass')},
                        {field: 'compsk', title: __('Compsk'),visible: false},
                        {field: 'compvk', title: __('Compvk'),visible: false},
                        {field: 'compaddr', title: __('Compaddr'),visible: false},
                        {field: 'comptel', title: __('Comptel'),visible: false},
                        {field: 'comptime', title: __('Comptime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'compjson', title: __('Compjson'),visible: false},
                        {field: 'compswitch', title: __('Compswitch'), table: table, formatter: Table.api.formatter.toggle,searchList: {"1": __('Able'), "0": __('Disable')}},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                    {
                                        name: 'reset',
                                        text:'',
                                        title:'重置密码',
                                        icon: 'fa fa-product-hunt',
                                        classname: 'btn btn-xs btn-success btn-ajax',
                                        url: 'comp/reset',
                                        success: function (data, ret) {
                                            Layer.alert("密码成功重置为：123456");
                                            return false;
                                        },
                                        error: function (data, ret) {
                                            console.log(data, ret);
                                            Layer.alert(ret.msg);
                                            return false;
                                        }
                                    },
                                    {
                                        name: 'clear',
                                        text:'',
                                        title: '解除微信绑定',
                                        icon: 'fa fa-repeat',
                                        classname: 'btn btn-xs btn-success btn-ajax',
                                        url: 'comp/clear',
                                        success: function (data, ret) {
                                            Layer.alert("绑定微信已经清空！");
                                            return false;
                                        },
                                        error: function (data, ret) {
                                            console.log(data, ret);
                                            Layer.alert(ret.msg);
                                            return false;
                                        }
                                    }
                                ],
                            formatter: Table.api.formatter.operate

                        },


                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'comp/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'comp/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'comp/destroy',
                                    refresh: true
                                }
                            ],
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        reset: function () {
            Controller.api.bindevent();
        },
        clear: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});