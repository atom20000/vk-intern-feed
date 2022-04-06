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
                    $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
                }
            }
            else
            {
                $response['description'] = 'The phone_number parameter must be provided';
                $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
            }
        }
        else
        {
            $response['description'] = 'HTTP method is not supported';
            $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
        }

        $responseHeaders[] = 'Content-Type: application/json';

        $this->sendOutput(json_encode($response), $responseHeaders);
    }

    /**
     * Endpoint `/phone/search_phones` - search for phone numbers
     * starting with a given pattern.
     */
    public function search_phonesAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $params = $this->getQueryStringParams();
        $response = [
            'error' => true
        ];
        $responseHeaders = [];

        if (strtoupper($method) === 'GET')
        {
            if (isset($params['phone_pattern']) && $params['phone_pattern'])
            {
                $numberModel = new NumberModel();
                $reviewModel = new ReviewModel();
                $matches = null;

                try
                {
                    $matches = $numberModel->matchPhone($params['phone_pattern']);
                }
                catch (UnexpectedValueException $ex)
                {
                    $response['description'] = 'Internal Server Error';
                    $responseHeaders[] = 'HTTP/1.1 500 Internal Server Error';
                    $responseHeaders[] = 'Content-Type: application/json';

                    $this->sendOutput($response, $responseHeaders);
                }

                if ($matches)
                {
                    $response['error'] = false;
                    $response['result'] = [
                        'phone_pattern' => $params['phone_pattern'],
                        'matches' => []
                    ];

                    foreach ($matches as $m)
                    {
                        $response['result']['matches'][] = [
                            'phone_number' => $m['number'],
                            'reviews_count' => count($reviewModel->getReviews($m['id']))
                        ];
                    }
                }
                else
                {
                    $response['description'] = 'Incorrect phone search pattern, no phones found';
                    $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
                }
            }
            else
            {
                $response['description'] = 'The phone_pattern parameter must be provided';
                $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
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
