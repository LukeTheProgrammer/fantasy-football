<?php

namespace App\Services\Espn\Resources;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseResource
{
    public array $currentRequest = [];

    public ?string $apiUrl = null;

    public ?string $apiVersion = null;

    public string $urlBase = '/sports/football/leagues/nfl/seasons/';

    public int $apiYear = 2025;

    public int $retryLimit = 2;

    public function __construct()
    {
        $this->apiUrl = config('services.espn.base_url');
        $this->apiVersion = config('services.espn.version');
        $this->retryLimit = config('service.espn.retry_limit');
    }

    public function get(string $url, array|string|null $query = null)
    {
        return $this->baseRequest()->get($url, $query);
    }

    public function post(string $url, array $data = [])
    {
        return $this->baseRequest()->post($url, $data);
    }

    public function put(string $url, array $data = [])
    {
        return $this->baseRequest()->put($url, $data);
    }

    public function patch(string $url, array $data = [])
    {
        return $this->baseRequest()->patch($url, $data);
    }

    public function delete(string $url, array $data = [])
    {
        return $this->baseRequest()->delete($url, $data);
    }

    public function headers(array $additional = []): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        return array_merge($headers, $additional);
    }

    public function query(array $additional = []): array
    {
        $query = [
            'lang'   => 'en',
            'region' => 'us',
        ];

        return array_merge($query, $additional);
    }

    public function baseRequest()
    {
        return Http::withHeaders($this->headers())
            ->beforeSending(fn (Request $req, array $opt) => $this->setRequestContext($req, $opt))
            ->throw(fn (Response $resp, RequestException $e) => $this->handleError($resp, $e))
            ->retry(
                $this->retryLimit,
                fn (int $attempt)        => $attempt * 100,
                fn (RequestException $e) => $this->canRetry($e),
            );
    }

    public function setRequestContext(Request $request, array $options)
    {
        $this->currentRequest = [
            'method'  => $request->method(),
            'url'     => $request->url(),
            'options' => Arr::only($options, [
                'connect_timeout',
                'crypto_method',
                'http_errors',
                'timeout',
                'laravel_data',
                'synchronous',
                'allow_redirects',
                'decode_content',
                'verify',
                'idn_conversion',
            ]),
        ];
    }

    public function handleError(Response $response, RequestException $e)
    {
        Log::error('ESPN Api Failure', [
            'context'  => $this->currentRequest,
            'response' => [
                'code'   => $response->status(),
                'body'   => $response->json(),
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

    public function buildUrl(string $endpoint)
    {
        $parts = [
            $this->apiUrl,
            $this->apiVersion,
            $this->urlBase,
            $this->apiYear,
            $endpoint,
        ];

        $url = implode('/', $parts);

        // Find and replace any double slashes, exempting http:// and https://
        // e.g. http://example.com/foo//bar -> http://example.com/foo/bar
        $url = preg_replace('/(?<!:)\/\//', '/', $url);

        return $url;
    }
}
