<?php

namespace Avreg;

require './OnvifClient/OnvifAjaxController.php';

class OnvifPtzController extends OnvifAjaxController
{
    /**
     * Connect to ONVIF-enabled camera by camera number.
     * Returns an array of relevant stored params,
     *
     * @param array $connectionData
     * @return array
     * @throws \Exception
     */
    protected function connectCamera($connectionData = array())
    {
        if (!isset($connectionData['cameraNumber'])) {
            throw new \Exception('cameraNumber not set');
        }

        // start of mess
        // todo - refactor, implement more efficient way to get camera params
        require('utils.php');
        require('/etc/avreg/site-defaults.php');

        $res = confparse($conf, 'avreg-site');
        if (!$res) {
            die();
        } else {
            $conf = array_merge($conf, $res);
        }

        if (!empty($profile) && $res = confparse($conf, 'avreg-site', $conf['profiles-dir'] . '/' . $profile)) {
            $conf = array_merge($conf, $res);
        }

        $link = null;
        $non_config = true;
        require_once($conf['site-dir'] . '/lib/adb.php');

        $camsData = $adb->getCamParams(
            $connectionData['cameraNumber'],
            "'text_left', 'InetCam_IP', 'InetCam_http_port', 'InetCam_USER', 'InetCam_PASSWD', 'onvif_profile_token'"
        );

        $camData = array();

        foreach ($camsData as $row) {
            if ($row['CAM_NR'] === $connectionData['cameraNumber']) {
                $camData[$row['PARAM']] = $row['VALUE'];
            }
        }
        // end of mess

        $connectionData = array(
            'origin' => 'http://' . $camData['InetCam_IP'] . ':' . '80',
            'username' => $camData['InetCam_USER'],
            'password' => $camData['InetCam_PASSWD'],
        );

        $this->connect($connectionData);

        $cameraParams = array(
            'profile_token' => $camData['onvif_profile_token']
        );

        return $cameraParams;
    }

    public function getPtzStatus($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $ptzStatus = $this->onvifClient->doSoapRequest(
            'ptz',
            'GetStatus',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($ptzStatus['isOk']) {
            $this->success(array(
                'PTZStatus' => $ptzStatus['result']->PTZStatus
            ));
        } else {
            $this->error('Could not get PTZ status.');
        }
    }

    public function getPtzPresets($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $ptzPresets = $this->onvifClient->doSoapRequest(
            'ptz',
            'GetPresets',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($ptzPresets['isOk']) {
            foreach ($ptzPresets['result']->Preset as $preset) {
                $preset->PTZPosition->PanTilt->x = sprintf('%f', $preset->PTZPosition->PanTilt->x);
                $preset->PTZPosition->PanTilt->y = sprintf('%f', $preset->PTZPosition->PanTilt->y);
                $preset->PTZPosition->Zoom->x = sprintf('%f', $preset->PTZPosition->Zoom->x);
            }

            $this->success(array(
                'Presets' => $ptzPresets['result']->Preset
            ));
        } else {
            $this->error();
        }
    }

    public function moveAbsolute($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['pan']) && !isset($data['tilt']) && !isset($data['zoom'])) {
            throw new \Exception('Position not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        // collect move coordinates
        $position = array(
            'PanTilt' => array(),
            'Zoom' => array()
        );

        isset($data['pan']) ? $position['PanTilt']['x'] = $data['pan'] : '';
        isset($data['tilt']) ? $position['PanTilt']['y'] = $data['tilt'] : '';
        isset($data['zoom']) ? $position['Zoom']['x'] = $data['zoom'] : '';

        // collect speed data
        $speed = array();

        if (isset($data['panSpeed']) || isset($data['tiltSpeed'])) {
            $speed['PanTilt'] = array();

            isset($data['panSpeed']) ? $speed['PanTilt']['x'] = $data['panSpeed'] : '';
            isset($data['tiltSpeed']) ? $speed['PanTilt']['y'] = $data['tiltSpeed'] : '';
        }

        if (isset($data['zoomSpeed'])) {
            $speed['Zoom'] = array('x' => $data['zoomSpeed']);
        }

        // do the request
        $requestParams = array(
            'Position' => $position,
            'Speed' => $speed,
            'ProfileToken' => $cameraParams['profile_token']
        );

        if (!empty($speed)) {
            $requestParams['Speed'] = $speed;
        }

        $moveResponse = $this->onvifClient->doSoapRequest(
            'ptz',
            'AbsoluteMove',
            $requestParams
        );

        if ($moveResponse['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function gotoPreset($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['presetToken'])) {
            throw new \Exception('presetToken not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $gotoResult = $this->onvifClient->doSoapRequest(
            'ptz',
            'GotoPreset',
            array('PresetToken' => $data['presetToken'], 'ProfileToken' => $cameraParams['profile_token'])
        );

        if ($gotoResult['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function createPreset($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['presetName'])) {
            throw new \Exception('presetName not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $result = $this->onvifClient->doSoapRequest(
            'ptz',
            'SetPreset',
            array('PresetName' => $data['presetName'], 'ProfileToken' => $cameraParams['profile_token'])
        );

        if ($result['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function removePreset($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['presetToken'])) {
            throw new \Exception('presetToken not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $result = $this->onvifClient->doSoapRequest(
            'ptz',
            'RemovePreset',
            array('PresetToken' => $data['presetToken'], 'ProfileToken' => $cameraParams['profile_token'])
        );

        if ($result['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }
}

$controller = new OnvifPtzController();
