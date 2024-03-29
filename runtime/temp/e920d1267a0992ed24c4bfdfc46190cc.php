<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:62:"F:\WebWork\car\public/../application/admin\view\dept\edit.html";i:1595199165;s:57:"F:\WebWork\car\application\admin\view\layout\default.html";i:1595199169;s:54:"F:\WebWork\car\application\admin\view\common\meta.html";i:1595199164;s:56:"F:\WebWork\car\application\admin\view\common\script.html";i:1595199164;}*/ ?>
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
                                <form id="edit-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Deptname'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <input id="c-deptname" class="form-control" name="row[deptname]" type="text" value="<?php echo htmlentities($row['deptname']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Comp_id'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            <input id="c-comp_id" data-rule="required" data-source="comp/get_type_list" class="form-control selectpage" name="row[comp_id]" type="text" value="<?php echo htmlentities($row['comp_id']); ?>" data-field="compname">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Deptaddr'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-deptaddr" class="form-control" name="row[deptaddr]" type="text" value="<?php echo htmlentities($row['deptaddr']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Deptjson'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
            
            <dl class="fieldlist" data-name="row[deptjson]">
                <dd>
                    <ins><?php echo __('Key'); ?></ins>
                    <ins><?php echo __('Value'); ?></ins>
                </dd>
                <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                <textarea name="row[deptjson]" class="form-control hide" cols="30" rows="5"><?php echo htmlentities($row['deptjson']); ?></textarea>
            </dl>


        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Deptswitch'); ?>:</label>
        <div class="col-xs-12 col-sm-5">
<!--            <input id="c-deptswitch" data-rule="required" class="form-control" name="row[deptswitch]" type="number" value="<?php echo htmlentities($row['deptswitch']); ?>">-->
            <?php echo build_radios('row[deptswitch]', ['1'=>__('Normal'), '0'=>'禁用'], $row['deptswitch']); ?>
        </div>
    </div>
    <div class="form-group layer-footer">
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