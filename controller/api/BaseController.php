<?php

class BaseController
{
    /**
     * Called when you try to call method that does not exist.
     * Used to return error on unresolved endpoint call.
     */
    public function __call($name, $arguments)
    {
        $this->sendOutput(
            json_encode(
                [
                    'error' => true,
                    'description' => 'Unable to resolve ' . $name
                ]
            ),
            [
                'HTTP/1.1 404 Not Found',
                'Content-Type: application/json'
            ]);
    }

    /**
     * It is useful when we try to validate
     * the REST endpoint called by the user.
     *
     * @return array
     */
    protected function getUriSegments(): array
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        return explode('/', $uri);
    }

    /**
     * Get query string parameters into $query
     * which are passed along with the incoming request.
     *
     * @return array
     */
    protected function getQueryStringParams(): array
    {
        if (isset($_SERVER['QUERY_STRING']))
        {
            parse_str($_SERVER['QUERY_STRING'], $query);

            return $query;
        }
        return [];
    }

    /**
     * Send API output.
     *
     * @param mixed $data
     * @param array $httpHeaders
     * @return void
     */
    protected function sendOutput($data, array $httpHeaders = [])
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
}
