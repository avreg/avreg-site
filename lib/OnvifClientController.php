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
        if (!isset($data['host'])) {
            throw new \Exception('Host not set');
        }
        if (!isset($data['path'])) {
            $data['path'] = '';
        }
        if (isset($data['username']) && !empty($data['username'])) {
            $credentials = true;
        }

        $site_prefix = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/lib/OnvifClientController.php'));
        $this->onvifClient = new \OnvifClient(
            $data['host'] . $data['path'],
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

    public function getBasicInfo($data = array())
    {
        $this->connect($data);

        $dateTime = $this->onvifClient->doSoapRequest(
            \OnvifServices::DEVICEMANAGEMENT,
            'GetSystemDateAndTime',
            array(),
            false
        );
        $capabilities = $this->onvifClient->doSoapRequest(\OnvifServices::DEVICEMANAGEMENT, 'GetCapabilities');
        $deviceInfo = $this->onvifClient->doSoapRequest(\OnvifServices::DEVICEMANAGEMENT, 'GetDeviceInformation');
        $services = $this->onvifClient->doSoapRequest(
            \OnvifServices::DEVICEMANAGEMENT,
            'GetServices',
            array('IncludeCapability' => true)
        );

        $this->success(array(
            'GetSystemDateAndTime' => $dateTime['isOk'] ? $dateTime['result'] : null,
            'GetCapabilities' => $capabilities['isOk'] ? $capabilities['result'] : null,
            'GetDeviceInformation' => $deviceInfo['isOk'] ? $deviceInfo['result'] : null,
            'GetServices' => $services['isOk'] ? $services['result'] : null
        ));
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

        $result = array();

        $profiles = $this->onvifClient->doSoapRequest(\OnvifServices::MEDIA, 'GetProfiles');

        if ($profiles['isOk']) {
            foreach ($profiles['result']->Profiles as $profile) {
                $protocols = array($data['TransportProtocol']);

                foreach ($protocols as $protocol) {
                    $streamUri = $this->onvifClient->doSoapRequest(
                        \OnvifServices::MEDIA,
                        'GetStreamUri',
                        array(
                            'StreamSetup' => array(
                                'Stream' => $data['StreamType'],
                                'Transport' => array(
                                    'Protocol' => $protocol
                                )
                            ),
                            'ProfileToken' => $profile->token
                        )
                    );

                    if ($streamUri['isOk']) {
                        $result[] = array(
                            'profile' => $profile->token,
                            'protocol' => $protocol,
                            'stream' => $streamUri['result']->MediaUri
                        );
                    }

                }
            }
        }

        $this->success(array(
            'MediaUri' => $result
        ));
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
