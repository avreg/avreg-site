<?php

namespace Avreg;

require (__DIR__ . '/OnvifClient.php');
require (__DIR__ . '/../AjaxController.php');

class OnvifAjaxController extends AjaxController
{
    /**
     * @type \OnvifClient
     */
    protected $onvifClient;

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

        global $conf;

        $site_prefix = $conf['prefix'];
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
}
