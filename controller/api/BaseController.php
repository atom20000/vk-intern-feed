<?php

class BaseController
{
    /**
     * Magic method.
     * Called when you try to call method that does not exist.
     * Use this to throw the Not Found error on unimplemented method call.
     */
    public function __call($name, $arguments)
    {
        $this->sendOutput('', ['HTTP/1.1 404 Not Found']);
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
        parse_str($_SERVER['QUERY_STRING'], $query);

        return $query;
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

        if (is_array($httpHeaders && count($httpHeaders)))
        {
            foreach ($httpHeaders as $h)
            {
                header($h); // TODO: does not return JSON
            }
        }

        echo $data;
        exit();
    }
}
