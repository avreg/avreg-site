<?php

require 'helpers.php';

class OnvifClientException extends Exception
{
}

class OnvifServices
{
    const DEVICEMANAGEMENT = 'devicemgmt';
    const MEDIA = 'media';
    const PTZ = 'ptz';
}

class OnvifClient
{
    /**
     * @var SoapClient
     */
    private $soap;
    /**
     * Instances of SoapClient for each service.
     * @var array
     */
    private $soapClients = array();

    private $deltatime;

    private $credentials = array();
    private $headers = array();

    private $logSoapRequests = false;
    private $loggedSoapRequests = array();

    private $lastRequestService;
    private $lastRequestTime;
    private $lastRequestMethodName;
    private $lastRequestSuccess;

    private $defaultOptions = array(
        'logSoapRequests' => true,
        'wsdlLocation' => null
    );

    /**
     * Options:
     * logSoapRequests    boolean
     *        Log all xml conversation (in memory, for now).
     * @param $serviceLocation        URI to service
     * @param $wsdlLocation            URI to path containing all wsdls
     * @param array $options
     * @throws OnvifClientException
     */
    public function __construct($serviceLocation, $wsdlLocation, $options = array())
    {
        $options = array_merge($this->defaultOptions, $options);

        if (filter_var($serviceLocation, FILTER_VALIDATE_URL) === false) {
            throw new OnvifClientException('Not a valid serviceLocation, must be an URI, found : ' . $serviceLocation);
        }
        $this->serviceLocation = $serviceLocation;

        if (filter_var($wsdlLocation, FILTER_VALIDATE_URL) === false) {
            throw new OnvifClientException('Not a valid wsdlLocation, must be an URI, found  : ' . $wsdlLocation);
        }
        $this->wsdlLocation = $wsdlLocation;

        $this->logSoapRequests = $options['logSoapRequests'];

        $this->soap = $this->getSoapClientForService(OnvifServices::DEVICEMANAGEMENT);

        $this->connect();
    }

    protected function getSoapClientForService($service)
    {
        if (!isset($this->soapClients[$service])) {
            $this->soapClients[$service] = new SoapClient(
                $this->getWsdlForService($service),
                array(
                    'location' => $this->serviceLocation,
                    'soap_version' => SOAP_1_2,
                    'trace' => 1
                )
            );
        }

        return $this->soapClients[$service];
    }

    protected function getWsdlForService($service)
    {
        return $this->wsdlLocation . '/' . $service . ".wsdl";
    }

    public function setCredentials($username, $password)
    {
        $this->credentials = array(
            'username' => $username,
            'password' => $password,
        );
    }

    public function hasCredentials()
    {
        return empty($this->credentials);
    }

    public function connect()
    {
        // store delta from server time, need for generating security nonce
        $response = $this->doSoapRequest(OnvifServices::DEVICEMANAGEMENT, 'GetSystemDateAndTime', array(), false);

        if ($response['isOk'] === true) {
            $response = object2array($response['result']);
        } else {
            throw $response['exception'];
        }

        $deviceDateTime = $response['SystemDateAndTime']['UTCDateTime'];

        $timestamp = mktime(
            $deviceDateTime['Time']['Hour'],
            $deviceDateTime['Time']['Minute'],
            $deviceDateTime['Time']['Second'],
            $deviceDateTime['Date']['Month'],
            $deviceDateTime['Date']['Day'],
            $deviceDateTime['Date']['Year']
        );

        $this->deltatime = time() - $timestamp - 5;
    }

    private function makeToken()
    {
        $timestamp = time() - $this->deltatime;
        return $this->passwordDigest(
            $this->credentials['username'],
            $this->credentials['password'],
            date('Y-m-d\TH:i:s.000\Z', $timestamp)
        );
    }

    private function passwordDigest($username, $password, $timestamp = "default", $nonce = "default")
    {
        if ($timestamp == 'default') {
            $timestamp = date('Y-m-d\TH:i:s.000\Z');
        }
        if ($nonce == 'default') {
            $nonce = mt_rand();
        }
        $digest = array();
        $passdigest = base64_encode(
            pack('H*', sha1(pack('H*', $nonce) . pack('a*', $timestamp) . pack('a*', $password)))
        );
        //$passdigest=base64_encode(sha1($nonce.$timestamp.$password,true)); // alternative
        $digest['username'] = $username;
        $digest['passwordDigest'] = $passdigest;
        $digest['nonce'] = base64_encode(pack('H*', $nonce));
        //$REQ['NONCE']=base64_encode($nonce); // alternative
        $digest['timestamp'] = $timestamp;
        return $digest;
    }

    private function setAuthHeaders()
    {
        if (isset($this->credentials['username']) && isset($this->credentials['password'])) {
            $token = $this->makeToken();
            $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

            /* ------------------------------------------------------------------------------------------------------ */
            // more proper way of creating
            // need adjustment

            /*
            $objSoapVarWSSEAuth = new SoapVar(
                array(
                    'Username' => new SoapVar($this->credentials['username'], XSD_STRING, null, null, null, $ns),
                    'Password' => new SoapVar($token['passwordDigest'], XSD_STRING, 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest', null, null, $ns),
                    'Nonce' => new SoapVar($token['nonce'], XSD_STRING, null, $ns, null, $ns),
                    'Created' => new SoapVar($token['timestamp'], XSD_STRING, null, null, null, "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"),
                ),
                SOAP_ENC_OBJECT,  null, $ns, null, $ns);

            $objSoapVarWSSEToken = new SoapVar(
                array('UsernameToken' => $objSoapVarWSSEAuth), SOAP_ENC_OBJECT,  null, $ns, null, $ns
            );

            $objSoapVarWSSEHeader = new SoapHeader(null, 'Security', $objSoapVarWSSEToken, true);

            // todo - find out why can't set headers as array
            $this->headers = $objSoapVarWSSEHeader;*/

            /* ------------------------------------------------------------------------------------------------------ */

            $xml = '
                <wsse:Security env:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                    <wsse:UsernameToken>
                        <wsse:Username>' . $token['username'] . '</wsse:Username>
                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">' . $token['passwordDigest'] . '</wsse:Password>
                        <wsse:Nonce>' . $token['nonce'] . '</wsse:Nonce>
                        <wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' . $token['timestamp'] . '</wsu:Created>
                    </wsse:UsernameToken>
                </wsse:Security>
            ';

            $this->headers = new SoapHeader($ns, 'Security', new SoapVar($xml, XSD_ANYXML), true);

        } else {
            throw new OnvifClientException('User credentials are incorrect.');
        }
    }

    public function doSoapRequest($service, $action, $params = array(), $auth = true)
    {
        try {
            if ($auth) {
                $this->setAuthHeaders();
            }

            $this->lastRequestService = $service;
            $this->lastRequestTime = time();
            $this->lastRequestMethodName = $action;

            $client = $this->getSoapClientForService($service);

            $client->__setSoapHeaders($this->headers);
            $result = call_user_func(array($client, $action), $params);

            $this->lastRequestSuccess = true;

            if ($this->logSoapRequests) {
                $this->loggedSoapRequests[] = $this->soapDebug();
            }

            // flush headers
            $this->headers = array();

            return array(
                'isOk' => true,
                'result' => $result,
                'debug' => $this->soapDebug()
            );
        } catch (Exception $e) {
            if ($e instanceof SoapFault or $e instanceof OnvifClientException) {
                if ($this->logSoapRequests) {
                    $this->lastRequestSuccess = false;
                    $this->loggedSoapRequests[] = $this->soapDebug();
                }

                return array(
                    'isOk' => false,
                    'exception' => $e
                );
            }
        }
    }

    public function getLoggedSoapRequests()
    {
        return $this->loggedSoapRequests;
    }

    /**
     * @return array
     */
    public function soapDebug()
    {
        $client = $this->getSoapClientForService($this->lastRequestService);

        return array(
            'serviceName' => $this->lastRequestService,
            'requestSuccess' => $this->lastRequestSuccess,
            'requestTime' => date("Y-m-d H:i:s", $this->lastRequestTime),
            'methodName' => $this->lastRequestMethodName,
            'requestHeaders' => $client->__getLastRequestHeaders(),
            'request' => $client->__getLastRequest(),
            'responseHeaders' => $client->__getLastResponseHeaders(),
            'response' => $client->__getLastResponse()
        );
    }

    public function soapDebugHtmlOutput()
    {
        $debug = $this->soapDebug();

        return $output = '<code>' . nl2br(htmlspecialchars($debug['requestHeaders'], true)) . '</code>'
            . highlight_string(xmlpp($debug['request']), true) . "<br/>\n"
            . '<code>' . nl2br(htmlspecialchars($debug['responseHeaders'], true)) . '</code>' . "<br/>\n"
            . highlight_string(xmlpp($debug['response']), true) . "<br/>\n";
    }
}
