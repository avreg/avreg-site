<?php

namespace Avreg;

require './OnvifClient/OnvifClient.php';

class AjaxController
{
    /**
     * @type \OnvifClient
     */
    private $onvifClient;

    public function __construct()
    {
        $method = $_POST['method'] ? : $_GET['method'] ? : null;
        $data = $_POST['data'] ? : $_GET['data'] ? : null;

        if (empty($method)) {
            $this->error('Method not found');
            return;
        }

        try {
            // todo harden security - allow to call only white-listed methods
            $this->{$method}($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function connect($data = array())
    {
        if (!isset($data['origin'])) {
            throw new \Exception('Origin not set');
        }
        if (!isset($data['path'])) {
            $data['path'] = '/onvif/device_service';
        }
        if (isset($data['username']) && !empty($data['username'])) {
            $credentials = true;
        }

        $site_prefix = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/lib/OnvifClientController.php'));
        $this->onvifClient = new \OnvifClient(
            $data['origin'] . $data['path'],
            "http://127.0.0.1$site_prefix/lib/OnvifClient/wsdl",
            array(
                'logSoapRequests' => true
            )
        );

        if (isset($credentials)) {
            $this->onvifClient->setCredentials($data['username'], isset($data['password']) ? $data['password'] : '');
        }
    }

    private function checkAuthData()
    {
        // dumb way of checking authorization
        $capabilities = $this->onvifClient->doSoapRequest(\OnvifServices::DEVICEMANAGEMENT, 'GetCapabilities');
        return $capabilities['isOk'];
    }

    public function checkConnection($data = array())
    {
        $this->connect($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
        } else {
            $this->success();
        };
    }

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

    public function doRequest($data = array())
    {
        // todo
    }

    protected function success($data = array())
    {
        header('Content-Type: application/json');
        header('HTTP/1.1 200 OK', true, 200);
        echo json_encode(array_merge(
            $data,
            array(
                '__loggedRequests' => isset($this->onvifClient) ? $this->onvifClient->getLoggedSoapRequests() : array()
            )
        ));
    }

    protected function error($message = '', $code = 400)
    {
        header('Content-Type: application/json');

        switch ($code) {
            case 401:
                header('HTTP/1.1 401 Unauthorized', true, 401);
                break;
            default:
            case 400:
                header('HTTP/1.1 400 Bad Request', true, 400);
                break;
        }

        echo json_encode(array(
            'message' => $message,
            'code' => $code,
            '__loggedRequests' => isset($this->onvifClient) ? $this->onvifClient->getLoggedSoapRequests() : array()
        ));
    }
}

$controller = new AjaxController();
