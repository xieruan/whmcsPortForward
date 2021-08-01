<link href="modules/addons/PortForward/static/css/jquery-confirm.css" rel="stylesheet">
<script src="modules/addons/PortForward/static/js/jquery-confirm.js"></script>
<div class="row">
    <div class="loading">
        <h1 style="text-align:center;color: #8f8f8f;font-weight: 500;font-size: 48px">
            <i class="fa fa-refresh fa-spin"></i> 请稍后 , 正在加载数据
        </h1>
    </div>
    <div class="nat-body" style="display:none">
        <div class="col-xs-15">
            <div class="row">
                <div class="col-md-12 col-xs-15">
                    <div class="panel panel-default" id="service-panel" style="display:none">
                        <div class="panel-body">
                            <h2 style="text-align:center;">{$productname}</h2>
                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <label>最大可转发端口数量</label>
                                    <div class="progress" style="margin-bottom: 0px;">
                                        <div class="progress-bar" role="progressbar" style="min-width: 7em;">
                                        </div>
                                    </div>
                                    <label>流量信息</label>
                                    <div class="progress" style="margin-bottom: 0px;">
                                        <div class="progress-bar" role="progressbar2" style="min-width: 7em;">
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-xs-12" style="margin-top: 10px;">
                                        <button style="float:left" type="button" class="btn btn-primary">添加规则
                                            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                        </button>
                                        <a style="float:right" class="btn btn-primary" href="javascript:void(0)"
                                            onclick="buy_traffic(this)">购买流量
                                            <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="margin-top: 10px;">
                            <table class="table table-hover table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>目标域名/IP</th>
                                        <th>目标端口</th>
                                        <th>方向</th>
                                        <th>连接域名/IP</th>
                                        <th>连接端口</th>
                                        <th>节点</th>
                                        <th>转发程序</th>
                                        <th>转发速度</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>192.168.1.1</td>
                                        <td>123</td>
                                        <td>=></td>
                                        <td>1.1.1.1</td>
                                        <td>123</td>
                                        <th>nodex</th>
                                        <td>方法X</td>
                                        <td>test</td>
                                        <td>
                                            <button type="button" class="btn btn-danger">Delete
                                                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {if $announcements}
                        <div class="panel-body">
                            <p>{$announcements|unescape:"html"}</p>
                        </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .nat-body,
    .nat-body button {
        font-family: Microsoft YaHei Light, Microsoft YaHei;
    }
</style>

<script type="text/javascript">

var load_rules = function(obj){
    $.ajax({
        url: 'clientarea.php?action=productdetails&id={$serviceid}&ajax=true',
        data: {
            act: 'list_rule'
        }
    }).done(function (data) {
        if (data.result != 'success'){
            $(".loading h1").html(data.error)
            return false;
        }
        node = data.nodes;
        $('.loading').css('display', 'none')
        $('.nat-body').css('display', '')
        // 要改
        let portPrecent = data.rules.length / data.maximum_port * 100 + '%'
        let trafficPrecent = data.traffic_used / data.traffic_all * 100 + '%'
        $('#service-panel .progress .progress-bar').eq(0).width(portPrecent).text(data.rules.length + ' / ' + data.maximum_port)
        $('#service-panel .progress .progress-bar').eq(1).width(trafficPrecent).text(data.traffic_used + ' GB / ' + data.traffic_all + ' GB')
        $('#service-panel table tbody').empty()
        if (data.rules.length > 0) {
            $('#service-panel .table-responsive').css('display', '')
        } else {
            $('#service-panel .table-responsive').css('display', 'none')
        }
        data.rules.forEach(function (rule) {
            switch(rule.status){
                case "pending":
                    rule.status = '<span class="label label-warning"><i class="fa fa-cog fa-spin"></i> 创建中</span>'
                    break;
                case "created":
                    rule.status = '<span class="label label-success">已创建</span>'
                    break;
                case "deleting":
                    rule.status = '<span class="label label-danger"><i class="fa fa-cog fa-spin"></i> 删除中</span>'
                    break;
                case "changing":
                    rule.status = '<span class="label label-warning"><i class="fa fa-cog fa-spin"></i> 更新中</span>'
                    break;
                case "suspend":
                    rule.status = '<span class="label label-danger"><i class="fa fa-cog fa-spin"></i> 已暂停</span>'
                    break;
            }
            $('#service-panel table tbody').append('<tr id="rule-' + rule.id + '"></tr>')
            $('#rule-' + rule.id).append('<td>' + rule.forwarddomain + '</td>')
            $('#rule-' + rule.id).append('<td>' + rule.forwardport + '</td>')
            $('#rule-' + rule.id).append('<td><=</td>')

            var public_address = rule.remoteip
            if (rule.cname !== undefined){
                public_address = rule.remoteip
            }

            $('#rule-' + rule.id).append('<td>' + public_address + '</td>')
            $('#rule-' + rule.id).append('<td>' + rule.nodeport + '</td>')
            $('#rule-' + rule.id).append('<td>' + rule.nodename + '</td>')
            $('#rule-' + rule.id).append('<td>' + rule.method + '</td>')
            $('#rule-' + rule.id).append('<td>' + rule.bandwidth + 'Mbps' + '</td>')
            $('#rule-' + rule.id).append('<td>' + rule.status + '</td>')
            $('#rule-' + rule.id).append('<td><button type="button" class="btn btn-xs btn-danger" data-ruleid="' + rule.id + '" data-rule-string="' + rule.forwarddomain + ':' + rule.forwardport + '<=' + rule.remoteip + ':' + rule.nodeport + '</br>' + rule.method + '">删除<span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td>')
        })
        $('#service-panel').css('display', '')
        }).fail(function(){
        $(".loading h1").html("与服务器通讯时出现错误, 请稍后再试")
		})
        }
		
		
		var buy_traffic = function(obj){
        	$.confirm({
            title: '购买流量加油包',
            content: '' +
                '<form action="" class="formName">' +
				'<div class="form-group"><label>流量购买</label><select name="traffic" class="form-control"><option value="100">100G</option><option value="500">500G</option></select></div>' +
                '</form>',
            buttons: {
                formSubmit: {
                    text: '提交',
                    btnClass: 'btn-blue',
                    action: function () {
                        var traffic = this.$content.find('[name=traffic]').val();

                        $.confirm({
                            content: function () {
                                var self = this;
                                return $.ajax({
                                    url: 'clientarea.php?action=productdetails&id={$serviceid}&ajax=true&act=buy_traffic' + '&traffic=' + traffic,
                                    dataType: 'json',
                                }).done(function (response) {
                                    if (response.result == "success"){
                                        self.setType('green')
                                        self.setTitle('账单已生成，请<a href="viewinvoice.php?id=' + response.invoiceid + '" target="_blank">点击此处</a>支付');
                                    } else {
                                        self.setType('red')
                                        self.setTitle('错误');
                                        self.setContent(response.error);
                                    }

                                }).fail(function(){
                                    self.setType('red')
                                    self.setTitle('错误');
                                    self.setContent('与服务器通讯时出现错误, 请重试.');
                                });
                            }
                        });
                    }
                },
                cancel: {
                    text: '取消',
                }
            },
            onContentReady: function () {
                var jc = this;
                this.$content.find('form').on('submit', function (e) {
                    e.preventDefault();
                    jc.$$formSubmit.trigger('click');
                });
            }
			});
        }
        



        $('#service-panel .panel-body').on('click', 'button', function () {
            var serviceid = $('#service-panel').data('serviceid')
            var html = ''
            node.forEach(function (node_t) { 
                html += '<option value="' + node_t.node_id + '">' + node_t.node_name + '</option>'
            })
            $.confirm({
                title: '添加规则',
                content: '' +
					'<form action="" class="formName" >' +
					'<div class="form-group">' +
					'<div class="input-group">' +
					'<input type="text" class="form-control" name="dest_ip" placeholder="目标域名/IP">' +
					'<input type="number" class="form-control" name="dest_port" min="1" max="65535" placeholder="目标端口">' +
					'&nbsp;' + 
					'<input type="number" class="form-control" name="pub_port" min="1" max="65535" placeholder="转发端口"></div></div>' +
					'<div class="form-group"><label>转发节点</label><select name="pub_node" class="form-control">' + html + '</select></div>' +
					'<div class="form-group"><label>转发程序</label><select name="method" class="form-control"><option value="method1">iptables</option><option value="method2">Brook</option><option value="method3">TinyMapper</option><option value="method4">Gost</option><option value="method5">realm</option><option value="method6">ehco</option></select></div>' +
					'</form>',
                buttons: {
                    formSubmit: {
                        text: '提交',
                        btnClass: 'btn-blue',
                        action: function () {
                            var dest_ip = this.$content.find('[name=dest_ip]').val();
                            var dest_port = this.$content.find('[name=dest_port]').val();
                            var pub_port = this.$content.find('[name=pub_port]').val();
                            var method = this.$content.find('[name=method]').val();
                            var pub_node = this.$content.find('[name=pub_node]').val();
                            console.log(method)
                            if (!dest_port || !pub_port) {
                                $.alert('端口范围错误');
                                return false;
                            }


                            $.confirm({
                                content: function () {
                                    var self = this;
                                    return $.ajax({
                                        url: 'clientarea.php?action=productdetails&id={$serviceid}&ajax=true&act=add_rule' + '&dest_ip=' + dest_ip + "&dest_port=" + dest_port + "&pub_port=" + pub_port + "&method=" + method + '&pub_node=' + pub_node,
                                        dataType: 'json',
                                    }).done(function (response) {
                                        if (response.result == "success") {
                                            self.setType('green')
                                            self.setTitle('成功');
                                            self.setContent('添加成功');
                                            load_rules();
                                        } else {
                                            self.setType('red')
                                            self.setTitle('错误');
                                            self.setContent(response.error);
                                        }

                                    }).fail(function () {
                                        self.setType('red')
                                        self.setTitle('错误');
                                        self.setContent('与服务器通讯时出现错误, 请重试.');
                                    });
                                }
                            });
                        }
                    },
                    cancel: {
                        text: '取消',
                    }
                },
                onContentReady: function () {
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click');
                    });
                }
            });
        })
        $('#service-panel table tbody').on('click', 'button', function () {
            var ruleid = $(this).data('ruleid')
            $.confirm({
                title: '确认删除转发规则',
                content: '即将删除以下规则 <br/>' + $(this).data('rule-string'),
                buttons: {
                    confirm: {
                        text: '确认',
                        btnClass: 'btn-blue',
                        action: function () {
                            $.confirm({
                                content: function () {
                                    var self = this;
                                    return $.ajax({
                                        url: 'clientarea.php?action=productdetails&id={$serviceid}&ajax=true&act=del_rule' + "&ruleid=" + ruleid,
                                        dataType: 'json',
                                    }).done(function (response) {
                                        if (response.result == "success") {
                                            self.setType('green')
                                            self.setTitle('成功');
                                            self.setContent('删除成功');
                                            load_rules();
                                        } else {
                                            self.setType('red')
                                            self.setTitle('错误');
                                            self.setContent(response.error);
                                        }

                                    }).fail(function () {
                                        self.setType('red')
                                        self.setTitle('错误');
                                        self.setContent('与服务器通讯时出现错误, 请重试.');
                                    });
                                }
                            });
                        }
                    },
                    cancel: {
                        text: "取消"
                    },
                }
            });
        })
         


load_rules()
</script>