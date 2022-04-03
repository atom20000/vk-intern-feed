<?php

//require_once "BaseController.php";
//require_once PROJECT_ROOT . "/model/NumberModel.php";

class NumberController extends BaseController
{
    /**
     * Endpoint `/phone/countryCode` - get country code for a given number
     */
    public function countryCodeAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $params = $this->getQueryStringParams();

        if ($method === 'GET')
        {
            $numberModel = new NumberModel();

            // if (isset($params['phone_number']) && $params['phone_number'])

            $countryCode = $numberModel->getCountryCode("+7123");

            $this->sendOutput(
                json_encode([
                    "result" => $countryCode
                ]),
                array(
                    'Content-Type: application/json; charset=utf-8',
                    'HTTP/1.1 200 OK'
                )
            );
        }
        else
        {
            $errorDesc = 'Method not allowed';
            $errorHeader = 'HTTP/1.1 400 Bad Request';

            $this->sendOutput(
                json_encode([
                    'error' => $errorDesc
                ]),
                ['Content-Type: application/json; charset=utf-8', $errorHeader]
            );
        }
    }
}
