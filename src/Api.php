<?php
# MIT License
#
# Copyright (c) 2020 Upwind24
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

namespace Upwind24;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Wrapper to manage login and communication with Upwind24 WebAPI.
 *
 * @package  Upwind24
 * @category Upwind24
 */
class Api
{
    /**
     * Default WebAPI endpoint URL.
     */
    const DEFAULT_ENDPOINT = 'http://dev-api.upwind24.com';

    /**
     * Default WebAPI version.
     */
    const DEFAULT_VERSION = 'v1.0';

    /**
     * Contain selected API endpoint
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Contain selected API version
     *
     * @var string
     */
    protected $version;

    /**
     * Contain key with client identifier.
     *
     * @var string
     */
    protected $clientId;

    /**
     * Contain key with secret identifier.
     *
     * @var string
     */
    protected $secretId;

    /**
     * Contain http client connection
     *
     * @var Client
     */
    protected $httpClient;

    /**
     * Construct a new wrapper instance
     *
     * @param $clientId                 User's API identifier.
     * @param $secretId                 User's API secret identifier.
     * @param string $endpoint          URL of the API endpoint.
     * @param string $version           API version.
     * @param Client|null $httpClient   Instance of existing HTTP client.
     *
     * @throws Exception\InvalidParameterException
     */
    public function __construct(
        $clientId,
        $secretId,
        $version = self::DEFAULT_VERSION,
        $endpoint = self::DEFAULT_ENDPOINT,
        Client $httpClient = null
    ) {
        if (!$clientId) {
            throw new Exception\InvalidParameterException("API client identifier not provided");
        }

        if (!$secretId) {
            throw new Exception\InvalidParameterException("API secret identifier not provided");
        }

        if (!$httpClient) {
            $httpClient = new Client([
                'timeout'         => 30,
                'connect_timeout' => 5,
            ]);
        }

        $this->clientId = $clientId;
        $this->secretId = $secretId;
        $this->version = $version;
        $this->endpoint = $endpoint;
        $this->httpClient = $httpClient;
    }

    /**
     * Main method of this API wrapper. It will sign a given query and return its results.
     *
     * @param string $method                    HTTP request method (GET, POST, PUT, DELETE)
     * @param string $path                      Relative URL of API request
     * @param \stdClass|array|null $content     Body of the request
     * @param bool $authentication              Does the request require header authentication
     * @param array $headers                    List of additional
     *
     * @return ResponseInterface
     *
     * @throws ClientException
     */
    protected function rawCall($method, $path, $content = null, $authentication = true, $headers = [])
    {
        $url = rtrim($this->endpoint, '/') . '/' . trim($this->version, '/') . '/' . ltrim($path, '/');
        $method = strtoupper($method);

        $request = new Request($method, $url);
        if ($content && $method == 'GET') {
            $queryString = $request->getUri()->getQuery();

            $query = [];
            if ($queryString) {
                $queries = explode('&', $queryString);
                foreach ($queries as $element) {
                    $keyValueQuery = explode('=', $element, 2);
                    $query[$keyValueQuery[0]] = $keyValueQuery[1];
                }
            }

            $query = array_merge($query, (array)$content);

            // Require query arguments to dump true/false parameters correctly
            foreach ($query as $key => $value) {
                if ($value === false) {
                    $query[$key] = "false";
                } elseif ($value === true) {
                    $query[$key] = "true";
                }
            }

            $query = \GuzzleHttp\Psr7\build_query($query);

            $url = $request->getUri()->withQuery($query);
            $request = $request->withUri($url);
        } elseif ($content) {
            $body = json_encode($content, JSON_UNESCAPED_SLASHES);
            $request->getBody()->write($body);
        }

        $headers['Content-Type'] = 'application/json; charset=utf-8';
        if ($authentication) {
            $headers['X-U24-Client'] = $this->clientId;
            $sign = sha1($this->clientId.'+'.$this->secretId.'+'.$method.'+/'.trim(strtolower($path), '/'));
            $headers['X-U24-Signature'] = $sign;
        }

        try {
            return $this->httpClient->send($request, ['headers' => $headers]);
        } catch (ClientException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Decode a Response object body to an array
     *
     * @param ResponseInterface $response
     *
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody(), true);
    }

    /**
     * Wrap call to Upwind24 WebAPI GET requests
     *
     * @param string $path    API call path
     * @param array  $content Content to send as query parameters
     * @param array  $headers List of additional request headers
     *
     * @return array
     * @throws ClientException if http request is an error
     */
    public function get($path, $content = null, $headers = [])
    {
        return $this->decodeResponse(
            $this->rawCall("GET", $path, $content, true, $headers)
        );
    }

    /**
     * Wrap call to Upwind24 WebAPI POST requests
     *
     * @param string $path    API call path
     * @param array  $content Content to send inside body of request
     * @param array  $headers List of additional request headers
     *
     * @return array
     * @throws ClientException if http request is an error
     */
    public function post($path, $content = null, $headers = [])
    {
        return $this->decodeResponse(
            $this->rawCall("POST", $path, $content, true, $headers)
        );
    }

    /**
     * Wrap call to Upwind24 WebAPI PUT requests
     *
     * @param string $path    API call path
     * @param array  $content Content to send inside body of request
     * @param array  $headers List of additional request headers
     *
     * @return array
     * @throws ClientException if http request is an error
     */
    public function put($path, $content, $headers = [])
    {
        return $this->decodeResponse(
            $this->rawCall("PUT", $path, $content, true, $headers)
        );
    }

    /**
     * Wrap call to Upwind24 WebAPI DELETE requests
     *
     * @param string $path    API call path
     * @param array  $content Content to send inside body of request
     * @param array  $headers List of additional request headers
     *
     * @return array
     * @throws ClientException if http request is an error
     */
    public function delete($path, $content = null, $headers = [])
    {
        return $this->decodeResponse(
            $this->rawCall("DELETE", $path, $content, true, $headers)
        );
    }

    /**
     * Get the current client identifier
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Return instance of HTTP client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }
}
