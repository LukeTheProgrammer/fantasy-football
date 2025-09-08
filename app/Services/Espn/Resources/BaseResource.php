<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\ApiYears;
use App\Services\Espn\Enums\Games;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseResource
{
    public array $currentRequest = [];

    public ?ApiVersions $apiVersion = null;
    public ?ApiYears $apiYear = null;
    public ?Apis $api = null;
    public ?Games $game = null;
    public ?Leagues $league = null;
    public ?Sports $sport = null;

    public function get(string $url, array|string|null $query = null, array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->get($url, $query);
    }

    public function post(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->post($url, $data);
    }

    public function put(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->put($url, $data);
    }

    public function patch(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->patch($url, $data);
    }

    public function delete(string $url, array $data = [], array $cookies = [], array $headers = [])
    {
        return $this->baseRequest($cookies, $headers)->delete($url, $data);
    }

    public function headers(array $additional = []): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        return array_filter(array_merge($headers, $additional));
    }

    public function cookies(array $additional = []): array
    {
        $cookies = [];

        return array_filter(array_merge($cookies, $additional));
    }

    public function query(array $additional = []): array
    {
        $query = [
            'lang'   => 'en',
            'region' => 'us',
        ];

        return array_filter(array_merge($query, $additional));
    }

    public function baseRequest(array $cookies = [], array $headers = [])
    {
        return Http::withCookies($this->cookies($cookies), 'espn.com')
            ->withHeaders($this->headers($headers))
            ->beforeSending(fn (Request $req, array $opt) => $this->setRequestContext($req, $opt))
            ->throw(fn (Response $resp, RequestException $e) => $this->handleError($resp, $e))
            ->retry(
                config('services.espn.retry_limit'),
                fn (int $attempt)        => $attempt * 100,
                fn (RequestException $e) => $this->canRetry($e),
            );
    }

    public function setRequestContext(Request $request, array $options)
    {
        $this->currentRequest = [
            'method'  => $request->method(),
            'url'     => $request->url(),
            // 'options' => Arr::only($options, [
            //     'connect_timeout',
            //     'crypto_method',
            //     'http_errors',
            //     'timeout',
            //     'laravel_data',
            //     'synchronous',
            //     'allow_redirects',
            //     'decode_content',
            //     'verify',
            //     'idn_conversion',
            // ]),
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

    public function assembleUrl(array $parts)
    {
        $url = implode('/', array_filter($parts));

        // Find and replace any double slashes, exempting http:// and https://
        // e.g. http://example.com/foo//bar -> http://example.com/foo/bar
        $url = preg_replace('/(?<!:)\/\//', '/', $url);

        return $url;
    }
}
