<?php

namespace Avreg;

require_once(__DIR__ . '/../head-xhr.inc.php');
require_once(__DIR__ . '/AjaxController.php');
require_once(__DIR__ . '/PtzInterface.php');

class AxisPtzController extends AjaxController implements PtzInterface
{
    public $camurl;

    public function __construct()
    {
        $this->setupCamera();

        parent::__construct();
    }

    protected function setupCamera()
    {
        global $adb;

        if (!isset($_POST['data']) || !isset($_POST['data']['cameraNumber'])) {
            throw new \Exception('Could not get camera params.');
        } else {
            $cameraNumber = $_POST['data']['cameraNumber'];
        }

        $camsData = $adb->getCamParams(
            $cameraNumber,
            "'text_left', 'InetCam_IP', 'InetCam_http_port', 'InetCam_USER', 'InetCam_PASSWD', 'onvif_profile_token'"
        );

        $camData = array();

        foreach ($camsData as $row) {
            if ($row['CAM_NR'] === $cameraNumber) {
                $camData[$row['PARAM']] = $row['VALUE'];
            }
        }

        $this->camurl = "http://{$camData['InetCam_USER']}:{$camData['InetCam_PASSWD']}@" .
            "{$camData['InetCam_IP']}:{$camData['InetCam_http_port']}";
    }

    public function getPtzSpaces($data = array())
    {
        $re = file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?query=limits");
        preg_match_all('/([^=\s]+)=([^=\s]+)/', $re, $r);
        $r = array_combine($r[1], array_map('floatval', $r[2]));

        if ($re) {
            $this->success(array(
                'coordSpaces' => array(
                    'zoom' => array(
                        'min' => $r['MinZoom'],
                        'max' => $r['MaxZoom']
                    ),
                    'pan' => array(
                        'min' => $r['MinPan'],
                        'max' => $r['MaxPan']
                    ),
                    'tilt' => array(
                        'min' => $r['MinTilt'],
                        'max' => $r['MaxTilt']
                    )
                )
            ));
        } else {
            $this->error();
        }
    }

    public function getPtzStatus($data = array())
    {
        $re = file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?query=position");
        preg_match_all('/([^=\s]+)=([^=\s]+)/', $re, $r);
        $r = array_combine($r[1], array_map('floatval', $r[2]));


        if ($re) {
            $this->success(array(
                'position' => array(
                    'pan' => $r['pan'],
                    'tilt' => $r['tilt'],
                    'zoom' => $r['zoom'],
                )
            ));
        } else {
            $this->error();
        }
    }

    public function moveAbsolute($data = array())
    {
        $re = file_get_contents(
            $this->camurl . "/axis-cgi/com/ptz.cgi?zoom="
            . $data['zoom'] . "&pan=" . $data['pan'] . "&tilt=" . $data['tilt']
        );

        if ($re) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function moveStop($data = array())
    {
        $re = file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?move=stop");

        if ($re) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function getPtzPresets($data = array())
    {
        $this->error('Not implemented', 501);
    }

    public function createPreset($data = array())
    {
        $this->error('Not implemented', 501);
    }

    public function removePreset($data = array())
    {
        $this->error('Not implemented', 501);
    }

    public function gotoPreset($data = array())
    {
        $this->error('Not implemented', 501);
    }

    public function gotoHomePosition($data = array())
    {
        $re = file_get_contents("$this->camurl/axis-cgi/com/ptz.cgi?move=home");

        if ($re) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function setHomePosition($data = array())
    {
        $re = file_get_contents("$this->camurl/axis-cgi/com/ptzconfig.cgi?setserverpresetname=home&home=yes");

        if ($re) {
            $this->success();
        } else {
            $this->error();
        }
    }
}

$controller = new AxisPtzController();
