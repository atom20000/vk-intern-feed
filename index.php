<?php

require __DIR__ . "/inc/bootstrap.php";

function sendOutput($data, array $httpHeaders = [])
{
    header_remove('Set-Cookie');

    if (is_array($httpHeaders) && count($httpHeaders))
    {
        foreach ($httpHeaders as $h)
        {
            header($h);
        }
    }

    echo $data;
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if ((isset($uri[2]) && $uri[2] != 'phone' && $uri[2] != 'review') || !isset($uri[3]))
{
    sendOutput(
        json_encode(
            [
                'error' => true,
                'description' => 'Not Found'
            ]
        ),
        [
            'HTTP/1.1 404 Not Found',
            'Content-Type: application/json'
        ]
    );
}

switch ($uri[2])
{
    case 'phone':
        $numberController = new NumberController();
        $methodName = $uri[3] . 'Action';
        $numberController->{$methodName}();
        break;

    case 'review':
        $reviewController = new ReviewController();
        $methodName = $uri[3] . 'Action';
        $reviewController->{$methodName}();
        break;

    default:
        sendOutput(
            json_encode(
                [
                    'error' => true,
                    'description' => 'Not Found'
                ]
            ),
            [
                'HTTP/1.1 404 Not Found',
                'Content-Type: application/json'
            ]
        );
}
