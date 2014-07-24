<?php

namespace Avreg;

require_once(__DIR__ . '/../../head-xhr.inc.php');
require_once(__DIR__ . '/../OnvifClient/OnvifAjaxController.php');
require_once(__DIR__ . '/../AjaxController.php');
require_once(__DIR__ . '/PtzInterface.inc.php');

class OnvifPtzController extends OnvifAjaxController implements PtzInterface
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
        global $adb;

        if (!isset($connectionData['cameraNumber'])) {
            throw new \Exception('cameraNumber not set');
        }

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

        $connectionData = array(
            'origin' => 'http://' . $camData['InetCam_IP'] . ':' . '80' /* FIXME why hardly tcp/80 ? */,
            'username' => $camData['InetCam_USER'],
            'password' => $camData['InetCam_PASSWD'],
        );

        $this->connect($connectionData);

        $cameraParams = array(
            'profile_token' => $camData['onvif_profile_token']
        );

        return $cameraParams;
    }

    /**
     * Extract speed component from POST request to be used in SOAP.
     * @param array $data
     * @return array
     */
    protected function getSpeedVector($data = array())
    {
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

        return $speed;
    }

    public function getPtzSpaces($data = array())
    {
        $this->success(array(
            'coordSpaces' => array(
                'zoom' => array(
                    'min' => 0,
                    'max' => 1
                ),
                'pan' => array(
                    'min' => -1,
                    'max' => 1
                ),
                'tilt' => array(
                    'min' => -1,
                    'max' => 1
                )
            ),
            'speedSpaces' => array(
                'zoom' => array(
                    'min' => 0,
                    'max' => 1
                ),
                'pan' => array(
                    'min' => 0,
                    'max' => 1
                ),
                'tilt' => array(
                    'min' => 0,
                    'max' => 1
                )
            )
        ));
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
            $this->success(
                array(
                    'position' => array(
                        // convert from possible scientific notation to dot notation
                        'pan' => sprintf('%F', $ptzStatus['result']->PTZStatus->Position->PanTilt->x),
                        'tilt' => sprintf('%F', $ptzStatus['result']->PTZStatus->Position->PanTilt->y),
                        'zoom' => sprintf('%F', $ptzStatus['result']->PTZStatus->Position->Zoom->x)
                    )
                )
            );
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
            $result = array();

            foreach ($ptzPresets['result']->Preset as $preset) {
                $result[] = array(
                    'name' => $preset->Name,
                    'token' => $preset->token,
                    'position' => array(
                        // convert from possible scientific notation to dot notation
                        'pan' => sprintf('%F', $preset->PTZPosition->PanTilt->x),
                        'tilt' => sprintf('%F', $preset->PTZPosition->PanTilt->y),
                        'zoom' => sprintf('%F', $preset->PTZPosition->Zoom->x)
                    )
                );
            }

            $this->success($result);
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

        // collect move parameters

        $position = array(
            'PanTilt' => array(),
            'Zoom' => array()
        );

        isset($data['pan']) ? $position['PanTilt']['x'] = $data['pan'] : '';
        isset($data['tilt']) ? $position['PanTilt']['y'] = $data['tilt'] : '';
        isset($data['zoom']) ? $position['Zoom']['x'] = $data['zoom'] : '';

        $speed = $this->getSpeedVector($data);

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

    public function moveStop($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $stopResult = $this->onvifClient->doSoapRequest(
            'ptz',
            'Stop',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($stopResult['isOk']) {
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

        $speed = $this->getSpeedVector($data);

        $requestParams = array(
            'PresetToken' => $data['presetToken'],
            'ProfileToken' => $cameraParams['profile_token']
        );

        if (!empty($speed)) {
            $requestParams['Speed'] = $speed;
        }

        $gotoResult = $this->onvifClient->doSoapRequest(
            'ptz',
            'GotoPreset',
            $requestParams
        );

        if ($gotoResult['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function gotoHomePosition($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $speed = $this->getSpeedVector($data);

        $requestParams = array(
            'Speed' => $speed,
            'ProfileToken' => $cameraParams['profile_token']
        );

        if (!empty($speed)) {
            $requestParams['Speed'] = $speed;
        }

        $gotoResult = $this->onvifClient->doSoapRequest(
            'ptz',
            'GotoHomePosition',
            $requestParams
        );

        if ($gotoResult['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function setHomePosition($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $result = $this->onvifClient->doSoapRequest(
            'ptz',
            'SetHomePosition',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($result['isOk']) {
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
