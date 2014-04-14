<?php
/**
 * @file online/ptz/common.inc.php
 * @brief common firstly php-includes for all PTZ handler
 */

session_start();
if (isset($_SESSION['is_admin_mode'])) {
    unset($_SESSION['is_admin_mode']);
}

$NO_OB_END_FLUSH = true; // for setcookie()
if (empty($pageTitle)) {
    $pageTitle = 'PTZ';
}
$body_style = 'overflow: hidden;  overflow-y: hidden !important; padding: 0; margin: 0; width: 100%; height: 100%;';
/*
$css_links = array(
    'lib/js/third-party/jqModal.css',
    'online/online.css'
 );
*/
$USE_JQUERY = true;
$link_javascripts = array(
    'lib/js/third-party/jquery.mousewheel.min.js',
    'lib/js/third-party/json2.js'
);

$body_addons = 'scroll="no"';
$ie6_quirks_mode = true;
$IE_COMPAT='10';
if (empty($lang_file)) {
    $lang_file = '_ptz.php';
}

require_once('../../lib/config.inc.php');

$GCP_cams_list=$_REQUEST['cam_nr'];
$GCP_query_param_list = array(
    'text_left',
    'InetCam_IP',
    'InetCam_http_port',
    'InetCam_USER',
    'InetCam_PASSWD',
);
require('../../lib/get_cams_params.inc.php');
$cam_http_params = array_merge($GCP_def_pars, $GCP_cams_params[$cam_nr]);

if (!isset($ptzi)) {
    include_once("ptzi.inc.php");
    $ptzi=new AVReg\PTZi();
};
$ptzi->camurl="http://{$cam_http_params['InetCam_USER']}:{$cam_http_params['InetCam_PASSWD']}@" .
    "{$cam_http_params['InetCam_IP']}:{$cam_http_params['InetCam_http_port']}";
if (!empty($_REQUEST['get'])) {
    $result=array();
    $g=explode(',', $_REQUEST['get']);

    if (in_array('ptzf_bounds', $g)) {
        $g=array_merge($g, array('pan_bounds', 'tilt_bounds', 'zoom_bounds', 'focus_bounds'));
    };
    if (preg_grep('/_bounds$/', $g)) {
        $r=$ptzi->getBounds();
    };
    if (in_array('pan_bounds', $g)) {
        cpkeys($result, $r, 'pan_start', 'pan_end');
    };
    if (in_array('tilt_bounds', $g)) {
        cpkeys($result, $r, 'tilt_start', 'tilt_end');
    };
    if (in_array('zoom_bounds', $g)) {
        cpkeys($result, $r, 'zoom_start', 'zoom_end');
    };
    if (in_array('focus_bounds', $g)) {
        cpkeys($result, $r, 'focus_start', 'zoom_end');
    };

    if (in_array('ptzf', $g)) {
        $g=array_merge($g, array('pan', 'tilt', 'zoom', 'focus'));
    };
    if (array_intersect(array('pan', 'tilt', 'zoom', 'focus'), $g)) {
        $r=$ptzi->getPos();
    };
    if (in_array('pan', $g)) {
        cpkeys($result, $r, 'pan', 'pan');
    };
    if (in_array('tilt', $g)) {
        cpkeys($result, $r, 'tilt', 'tilt');
    };
    if (in_array('zoom', $g)) {
        cpkeys($result, $r, 'zoom', 'zoom');
    };
    if (in_array('focus', $g)) {
        cpkeys($result, $r, 'focus', 'zoom');
    };

    print json_encode($result);
    exit;
};
if (array_intersect(array('pan', 'tilt', 'zoom', 'focus'), array_keys($_REQUEST))) {
    if (!empty($_REQUEST['pan'])) {
        $ptzi->pan($_REQUEST['pan']);
    };
    if (!empty($_REQUEST['tilt'])) {
        $ptzi->tilt($_REQUEST['tilt']);
    };
    if (!empty($_REQUEST['zoom'])) {
        $ptzi->zoom($_REQUEST['zoom']);
    };
    if (!empty($_REQUEST['focus'])) {
        $ptzi->focus($_REQUEST['focus']);
    };
    exit;
};
if (!empty($_REQUEST['move'])) {
    if (in_array($_REQUEST['move'], array('left', 'right'))) {
        $ptzi->move($_REQUEST['move'], isset($_REQUEST['fast'])?'pan_fast':'pan_step');
    };
    if (in_array($_REQUEST['move'], array('up', 'down'))) {
        $ptzi->move($_REQUEST['move'], isset($_REQUEST['fast'])?'tilt_fast':'tilt_step');
    };
    if (in_array($_REQUEST['move'], array('wide', 'tele'))) {
        $ptzi->move($_REQUEST['move'], isset($_REQUEST['fast'])?'zoom_fast':'zoom_step');
    };
    if (in_array($_REQUEST['move'], array('close', 'far'))) {
        $ptzi->move($_REQUEST['move'], isset($_REQUEST['fast'])?'focus_fast':'focus_step');
    };
    if (in_array($_REQUEST['move'], array('home', 'stop'))) {
        $ptzi->move($_REQUEST['move']);
    };
    exit;
};
if (!empty($_REQUEST['home'])) {
    $ptzi->home($_REQUEST['home']);
    exit;
};
function is_capable($cap)
{
    global $ptz_caps;
    return (in_array($cap, $ptz_caps) and !isset($ptz_caps[$cap])) or !empty($ptz_caps[$cap]);
}
function cpkeys(&$dest, $src)
{

    for ($i=2; $i<func_num_args(); $i++) {
        if (isset($src[func_get_arg($i)])) {
            $dest[func_get_arg($i)]=$src[func_get_arg($i)];
        };
    };
}
?>
<script type="text/javascript">

$(function() {
    var $win;
    var $cam_nr;
    
    $cam_nr=<?php echo $_REQUEST['cam_nr']?>;
    $win=ptztemp_win;
    delete ptztemp_win;
    $('button img',$win).height('7px');
    $('button',$win).css('padding','0px');
    //$('button',$win).css('border','0px');
    $('td',$win).css('padding','0px');
    var script='ptz/<?php print basename($_SERVER['SCRIPT_NAME']) ?>';
    var slider_change=function() {
        var v=$(this).slider('value');
        if ($(this).hasClass('pan')) $.get(script,{cam_nr:$cam_nr,pan:v});
        if ($(this).hasClass('tilt')) $.get(script,{cam_nr:$cam_nr,tilt:v});
        if ($(this).hasClass('zoom')) $.get(script,{cam_nr:$cam_nr,zoom:v});
        if ($(this).hasClass('focus')) $.get(script,{cam_nr:$cam_nr,focus:v});
    };
    $.get(script,{cam_nr:$cam_nr,get:'ptzf_bounds,ptzf'},function(data){
        $('.ptz-slider.pan',$win).slider({ min:data.pan_start, max:data.pan_end, value:data.pan});
        $('.ptz-slider.tilt',$win).slider({ min:data.tilt_start, max:data.tilt_end, value:data.tilt,
            orientation: 'vertical'
        });
        $('.ptz-slider.zoom',$win).slider({ min:data.zoom_start, max:data.zoom_end, value:data.zoom});
        $('.ptz-slider.focus',$win).slider({ min:data.focus_start, max:data.focus_end, value:data.focus});
        $('.ptz-slider',$win).slider({
            change: slider_change
        });
        //$('.ptz-slider.pan',$win).slider({ range:min});
    },'json');

    if ($win.data('ptz-timer'))
        clearInterval($win.data('ptz-timer'));
    $win.data('ptz-timer',setInterval(function() {
        if (!$('.pl_ptz',$win).hasClass('active'))
        {
            clearInterval($win.data('ptz-timer'));
            return;
        };
        $.get(script,{cam_nr:$cam_nr,get:'ptzf'},function(data){
            $('.ptz-slider',$win).slider({ change:null});
            $('.ptz-slider.pan',$win).slider({ value:data.pan});
            $('.ptz-slider.tilt',$win).slider({ value:data.tilt});
            $('.ptz-slider.zoom',$win).slider({ value:data.zoom});
            $('.ptz-slider.focus',$win).slider({ value:data.focus});
            $('.ptz-slider',$win).slider({ change:slider_change});
        },'json');
    },1000));
    $('button.move',$win).click(function(e){
        if ($(this).hasClass('left')) $.get(script,{cam_nr:$cam_nr,move:'left'});
        if ($(this).hasClass('left-fast')) $.get(script,{cam_nr:$cam_nr,move:'left','fast':'1'});
        if ($(this).hasClass('right')) $.get(script,{cam_nr:$cam_nr,move:'right'});
        if ($(this).hasClass('right-fast')) $.get(script,{cam_nr:$cam_nr,move:'right','fast':'1'});
        if ($(this).hasClass('up')) $.get(script,{cam_nr:$cam_nr,move:'up'});
        if ($(this).hasClass('up-fast')) $.get(script,{cam_nr:$cam_nr,move:'up','fast':'1'});
        if ($(this).hasClass('down')) $.get(script,{cam_nr:$cam_nr,move:'down'});
        if ($(this).hasClass('down-fast')) $.get(script,{cam_nr:$cam_nr,move:'down','fast':'1'});
        if ($(this).hasClass('wide')) $.get(script,{cam_nr:$cam_nr,move:'wide'});
        if ($(this).hasClass('wide-fast')) $.get(script,{cam_nr:$cam_nr,move:'wide','fast':'1'});
        if ($(this).hasClass('tele')) $.get(script,{cam_nr:$cam_nr,move:'tele'});
        if ($(this).hasClass('tele-fast')) $.get(script,{cam_nr:$cam_nr,move:'tele','fast':'1'});
        if ($(this).hasClass('close')) $.get(script,{cam_nr:$cam_nr,move:'close'});
        if ($(this).hasClass('close-fast')) $.get(script,{cam_nr:$cam_nr,move:'close','fast':'1'});
        if ($(this).hasClass('far')) $.get(script,{cam_nr:$cam_nr,move:'far'});
        if ($(this).hasClass('far-fast')) $.get(script,{cam_nr:$cam_nr,move:'far','fast':'1'});
        if ($(this).hasClass('stop')) $.get(script,{cam_nr:$cam_nr,move:'stop'});
    });
    $('button.home',$win).click(function(e){
        if ($(this).hasClass('go')) $.get(script,{cam_nr:$cam_nr,move:'home'});
        if ($(this).hasClass('set')) $.get(script,{cam_nr:$cam_nr,home:'set'});
        if ($(this).hasClass('reset')) $.get(script,{cam_nr:$cam_nr,home:'reset'});
    });
    $('button',$win).click(function(e){
        e.stopPropagation();
    });
});

</script>

<div class="ptz_area_right">
<table height="100%">
    <tr>
<?php if (is_capable('tilt')) {?>
    <td height="10px"><button class="move up" title="Look up"><img src="ptz/u.png"></img></button></td>
    <?php 
}?>
<?php if (is_capable('home')) {?>
    <td rowspan="2" style="vertical-align:top">
    <div class=".ptz-home">
    <button class="home go" title="Go home">HOME</button>
    <div style="display:inline-block">
        <button class="home set" title="Set as home"><img src="ptz/sh.png"></button>
        <button class="home reset" title="Reset home"><img src="ptz/rh.png"></button>
    </div>
    </div><!-- ptz-home -->
    </td>
    <?php 
}?>
</tr>
<?php if (is_capable('tilt')) {?>
    <tr>
    <td height="10px"><button class="move up-fast" title="Look upper"><img src="ptz/uu.png"></img></button>
    </tr>
    <tr>
    <td height="100%" style="text-align:center">
        <div class="ptz-slider tilt" style="height:90%" title="Adjust tilt"/>
    </td>
    </tr>
    <tr>
    </tr>
    <tr>
    <td height="10px"><button class="move down-fast" title="Look downer"><img src="ptz/dd.png"></button>
    </tr>
    <tr>
    <td height="10px"><button class="move down" title="Look down"><img src="ptz/d.png"></button>
    </tr>
    <?php 
}?>
</table>
</div><!-- ptz_area_right -->

<div class="ptz_area_bottom">

<table width="100%" height="100%">
<tr height="100%">
<td height="100%">
<table width="100%" height="100%">
<?php if (is_capable('pan')) {?>
    <tr>
    <td width="10px"><button class="move left" title="Look left"><img src="ptz/l.png"></button></td>
    <td width="10px"><button class="move left-fast" title="Look more left"><img src="ptz/ll.png"></button></td>
    <td><div class="ptz-slider pan" style="margin-left:10px;margin-right:10px" title="Adjust pan"/></td>
    <td width="20px"><button class="move right-fast" title="Look more right"><img src="ptz/rr.png"></button></td>
    <td width="20px"><button class="move right" title="Look right"><img src="ptz/r.png"></button></td>
    </tr>
    <?php 
}?>
<?php if (is_capable('zoom')) {?>
    <tr>
    <td width="10px"><button class="move wide" title="Zoom out"><img src="ptz/l.png"></button></td>
    <td width="10px"><button class="move wide-fast" title="Zoom outer"><img src="ptz/ll.png"></button></td>
    <td><div class="ptz-slider zoom" style="margin-left:10px;margin-right:10px" title="Adjust zoom level"/></td>
    <td width="20px"><button class="move tele-fast" title="Zoom inner"><img src="ptz/rr.png"></button></td>
    <td width="20px"><button class="move tele" title="Zoom in"><img src="ptz/r.png"></button></td>
    </tr>
    <?php 
}?>
<?php if (is_capable('focus')) {?>
    <tr>
    <td width="10px"><button class="move close"><img src="ptz/l.png"></button></td>
    <td width="10px"><button class="move close-fast"><img src="ptz/ll.png"></button></td>
    <td><div class="ptz-slider focus" style="margin-left:10px;margin-right:10px"/></td>
    <td width="20px"><button class="move far-fast"><img src="ptz/rr.png"></button></td>
    <td width="20px"><button class="move far"><img src="ptz/r.png"></button></td>
    </tr>
    <?php 
}?>
    </table>
    </td>
<?php if (is_capable('stop')) {?>
    <td width="20%" style="text-align:center">
    <div class=".ptz-stop"><button class="move stop" title="Stop movement">STOP</button></div>
    </td>
    <?php 
}?>
    </tr>
</table>
</div><!-- ptz_area_bottom -->
