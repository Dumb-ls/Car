<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:67:"F:\WebWork\car\public/../application/admin\view\auth\admin\add.html";i:1595199160;s:57:"F:\WebWork\car\application\admin\view\layout\default.html";i:1595199169;s:54:"F:\WebWork\car\application\admin\view\common\meta.html";i:1595199164;s:56:"F:\WebWork\car\application\admin\view\common\script.html";i:1595199164;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="add-form" class="form-horizontal form-ajax" role="form" data-toggle="validator" method="POST" action="">
    <?php echo token(); ?>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Group'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <?php echo build_select('group[]', $groupdata, null, ['class'=>'form-control selectpicker', 'multiple'=>'', 'data-rule'=>'required']); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="username" class="control-label col-xs-12 col-sm-2">所属机构:</label>
        <div class="col-xs-12 col-sm-5">
            <input id="comp_id" data-rule="required" data-source="comp/get_type_list"  class="form-control selectpage" name="row[comp_id]" type="text" value="" data-field="compname">
        </div>
    </div>
    <div class="form-group">
        <label for="username" class="control-label col-xs-12 col-sm-2"><?php echo __('Username'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <input type="text" class="form-control" id="username" name="row[username]" value="" data-rule="required;username" />
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="control-label col-xs-12 col-sm-2"><?php echo __('Email'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <input type="email" class="form-control" id="email" name="row[email]" value="" data-rule="required;email" />
        </div>
    </div>
    <div class="form-group">
        <label for="nickname" class="control-label col-xs-12 col-sm-2"><?php echo __('Nickname'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <input type="text" class="form-control" id="nickname" name="row[nickname]" autocomplete="off" value="" data-rule="required" />
        </div>
    </div>
    <div class="form-group">
        <label for="password" class="control-label col-xs-12 col-sm-2"><?php echo __('Password'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <input type="password" class="form-control" id="password" name="row[password]" autocomplete="new-password" value="" data-rule="required;password" />
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <?php echo build_radios('row[status]', ['normal'=>__('Normal'), 'hidden'=>__('Hidden')]); ?>
        </div>
    </div>
    <div class="form-group hidden layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-5">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>