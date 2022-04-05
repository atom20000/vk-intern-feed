<?php

class NumberController extends BaseController
{
    /**
     * Endpoint `/phone/country_code` - get country code for a given number
     */
    public function country_codeAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $params = $this->getQueryStringParams();
        $response = [
            'error' => true
        ];
        $headers = [];

        if (strtoupper($method) === 'GET')
        {
            $numberModel = new NumberModel();

            if (isset($params['phone_number']) && $params['phone_number'])
            {
                $countryCode = null;

                try
                {
                    $countryCode = $numberModel->getCountryCode($params['phone_number']);
                }
                catch (UnexpectedValueException $ex)
                {
                    $response['description'] = 'Internal Server Error';
                    $headers[] = 'HTTP/1.1 500 Internal Server Error';
                    $headers[] = 'Content-Type: application/json';

                    $this->sendOutput($response, $headers);
                }

                // if result has been received, cancel error message
                if ($countryCode)
                {
                    $response['error'] = false;
                    $response['result'] = [
                        'phone_number' => $params['phone_number'],
                        'country_code' => $countryCode
                    ];
                    $headers[] = 'HTTP/1.1 200 OK';
                }
                else
                {
                    $response['description'] = 'Incorrect phone format or unavailable country';
                    $headers[] = 'HTTP/1.1 400 Bad Request';
                }
            }
        }
        else
        {
            $response['description'] = 'HTTP method is not supported';
            $headers[] = 'HTTP/1.1 400 Bad Request';
        }

        $headers[] = 'Content-Type: application/json';
        $this->sendOutput(json_encode($response), $headers);
    }
}
