<?php
/**
 * @file online/ptz/axis.php
 * @brief AXIS camera PTZ handler
 */
namespace AVReg;

$pageTitle = 'AXIS PTZ';
$ptz_caps=array('pan','tilt','zoom','home','stop');
$movements=array(
    'pan_step'=>10,'pan_fast'=>30,
    'tilt_step'=>5,'tilt_fast'=>15,
    'zoom_step'=>500,'zoom_fast'=>1500,
);

include "ptzi.inc.php";
class AXIS extends PTZi
{
    public function getBounds()
    {
        $re=file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?query=limits");
        preg_match_all('/([^=\s]+)=([^=\s]+)/', $re, $r);
        $r=array_combine($r[1], array_map('floatval', $r[2]));
        return array(
            'pan_start'=>$r['MinPan'], 'pan_end'=>$r['MaxPan'],
            'tilt_start'=>$r['MinTilt'], 'tilt_end'=>$r['MaxTilt'],
            'zoom_start'=>$r['MinZoom'], 'zoom_end'=>$r['MaxZoom']
        );
    }
    public function getPos()
    {
        $re=file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?query=position");
        preg_match_all('/([^=\s]+)=([^=\s]+)/', $re, $r);
        $r=array_combine($r[1], array_map('floatval', $r[2]));
        return array('pan'=>$r['pan'], 'tilt'=>$r['tilt'], 'zoom'=>$r['zoom']);

    }
    public function pan($value)
    {
        $value=preg_replace('/,/', '.', $value);
        $re=file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?pan=$value");
        print $re;
    }
    public function tilt($value)
    {
        $value=preg_replace('/,/', '.', $value);
        $re=file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?tilt=$value");
        print $re;
    }
    public function zoom($value)
    {
        $value=preg_replace('/,/', '.', $value);
        $re=file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?zoom=$value");
        print $re;
    }
    public function home($action)
    {
        if ($action=='go') {
            $re=file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?move=home");
        };
        if ($action=='set') {
            $re=file_get_contents("$this->camurl/axis-cgi/com/ptzconfig.cgi?setserverpresetname=home&home=yes");
        };
        if ($action=='reset') {
            $re=file_get_contents("$this->camurl/axis-cgi/com/ptzconfig.cgi?removeserverpresetname=home");
        };
        print $re;
    }
    public function stop()
    {
        $re=file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?move=stop");
        print $re;
    }
}

$ptzi=new AXIS();
$ptzi->movements=$movements;
include "common.inc.php";
