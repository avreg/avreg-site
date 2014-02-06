<?php

namespace Avreg;

require 'OnvifClient.php';

class OnvifAjaxController
{
    /**
     * @type \OnvifClient
     */
    protected $onvifClient;

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

        // this is not working - find a way to get prefix
        $site_prefix = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/lib/OnvifClientController.php'));
        // debug
        $site_prefix = '/avreg';
        $this->onvifClient = new \OnvifClient(
            $data['origin'] . $data['path'],
            "http://127.0.0.1$site_prefix/lib/OnvifClient/wsdl",
            array(
                'logSoapRequests' => false
            )
        );

        if (isset($credentials)) {
            $this->onvifClient->setCredentials($data['username'], isset($data['password']) ? $data['password'] : '');
        }
    }

    protected function checkAuthData()
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
