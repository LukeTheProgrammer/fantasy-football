<?php

namespace App\Traits;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait MakesHttpRequests
{
    /**
     * Undocumented variable
     *
     * @var array
     */
    public array $currentRequest = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    public array $defaultCookies = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    public array $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    /**
     * Undocumented variable
     *
     * @var array
     */
    public array $defaultQuery = [];

    /**
     * Undocumented variable
     *
     * @var string
     */
    public ?string $cookieDomain = null;

    /**
     * Undocumented variable
     *
     * @var int
     */
    public int $retryLimit = 3;

    /**
     * Make a GET request.
     */
    public function get(string $url, array|string|null $query = null, array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->get($url, $query);
    }

    /**
     * Make a POST request.
     */
    public function post(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->post($url, $data);
    }

    /**
     * Make a PUT request.
     */
    public function put(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->put($url, $data);
    }

    /**
     * Make a PATCH request.
     */
    public function patch(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->patch($url, $data);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->delete($url, $data);
    }

    /**
     * Build the base request.
     */
    public function baseRequest(array $cookies = [], array $headers = [])
    {
        $http = Http::withHeaders($this->headers($headers));

        if (! empty($this->cookieDomain) && ! empty($this->cookies($cookies))) {
            $http->withCookies($this->cookies($cookies), $this->cookieDomain);
        }

        $http->beforeSending(
            fn (Request $req, array $opt) => $this->setRequestContext($req, $opt)
        );

        $http->throw(
            fn (Response $resp, RequestException $e) => $this->handleError($resp, $e)
        );

        $http->retry(
            $this->retryLimit,
            fn (int $attempt) => $attempt * 100,
            fn (RequestException $e) => $this->canRetry($e),
        );

        return $http;
    }

    /**
     * Build headers for the request.
     */
    public function headers(array $additional = []): array
    {
        return array_filter(
            array_merge($this->defaultHeaders, $additional)
        );
    }

    /**
     * Build cookies for the request.
     */
    public function cookies(array $additional = []): array
    {
        return array_filter(
            array_merge($this->defaultCookies, $additional)
        );
    }

    /**
     * Build query parameters for the request.
     */
    public function query(array $additional = []): array
    {
        return array_filter(
            array_merge($this->defaultQuery, $additional)
        );
    }

    /**
     * Set the request context.
     */
    public function setRequestContext(Request $request, array $options)
    {
        $this->currentRequest = [
            'method' => $request->method(),
            'url'    => $request->url(),
        ];
    }

    /**
     * Handle a request error.
     */
    public function handleError(Response $response, RequestException $e)
    {
        Log::error('Http Request Failure', [
            'context'  => $this->currentRequest,
            'response' => [
                'code'   => $response->status(),
                'body'   => $response->body(),
                'reason' => $response->reason(),
            ],
            'exception' => [
                'class'   => get_class($e),
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ],
        ]);

        throw $e;
    }

    /**
     * Determine if a request can be retried.
     */
    public function canRetry(RequestException $e)
    {
        if ($e->response->requestTimeout()) {
            return true;
        }

        if ($e->response->serverError()) {
            return true;
        }

        return false;
    }

    /**
     * Assemble a URL from an array of parts.
     */
    public function assembleUrl(array $parts)
    {
        $url = implode('/', array_filter($parts));

        $url = stripMultipleSlashes($url);

        return $url;
    }
}
