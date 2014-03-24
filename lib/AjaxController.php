<?php

namespace Avreg;

require_once '../head-xhr.inc.php';

class AjaxController
{
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

    protected function success($data = array())
    {
        header('Content-Type: application/json');
        header('HTTP/1.1 200 OK', true, 200);
        echo json_encode($data);
    }

    protected function error($message = '', $code = 400)
    {
        header('Content-Type: application/json');

        switch ($code) {
            case 401:
                header('HTTP/1.1 401 Unauthorized', true, 401);
                break;
            case 501:
                header('HTTP/1.1 501 Not Implemented', true, 501);
                break;
            default:
            case 400:
                header('HTTP/1.1 400 Bad Request', true, 400);
                break;
        }

        echo json_encode(array(
            'message' => $message,
            'code' => $code
        ));
    }
}
