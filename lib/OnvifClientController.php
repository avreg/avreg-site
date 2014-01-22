<?php

namespace Avreg;

require './OnvifClient/OnvifAjaxController.php';

class OnvifClientController extends OnvifAjaxController
{
    public function getDeviceInfo($data = array())
    {
        $this->connect($data);

        $deviceInfo = $this->onvifClient->doSoapRequest(\OnvifServices::DEVICEMANAGEMENT, 'GetDeviceInformation');

        if ($deviceInfo['isOk']) {
            $this->success(array(
                'DeviceInformation' => $deviceInfo['result']
            ));
        } else {
            $this->error();
        }
    }

    public function getProfiles($data = array())
    {
        $this->connect($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $profiles = $this->onvifClient->doSoapRequest(\OnvifServices::MEDIA, 'GetProfiles');

        if ($profiles['isOk']) {
            $this->success(array(
                'Profiles' => $profiles['result']
            ));
        } else {
            $this->error();
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function getMediaUri($data = array())
    {
        $this->connect($data);

        if (!isset($data['ProfileToken'])) {
            throw new \Exception('ProfileToken not set');
        }

        if (!isset($data['StreamType'])) {
            throw new \Exception('StreamType not set');
        }

        if (!isset($data['TransportProtocol'])) {
            throw new \Exception('TransportProtocol not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $streamUri = $this->onvifClient->doSoapRequest(
            \OnvifServices::MEDIA,
            'GetStreamUri',
            array(
                'StreamSetup' => array(
                    'Stream' => $data['StreamType'],
                    'Transport' => array(
                        'Protocol' => $data['TransportProtocol']
                    )
                ),
                'ProfileToken' => $data['ProfileToken']
            )
        );

        if ($streamUri['isOk']) {
            $this->success(array(
                'MediaUri' => $streamUri['result']->MediaUri
            ));
        } else {
            $this->error();
        }
    }
}

$controller = new OnvifClientController();
