<?php

namespace App\Services;

use App\Exceptions\BackendApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BackendApiService
{
    protected PendingRequest $client;

    public function __construct()
    {
        $baseUrl = rtrim(config('services.backend.base_url'), '/');
        
        // Normalize URLs for comparison (handle localhost vs 127.0.0.1)
        $currentUrl = rtrim(config('app.url'), '/');
        $currentUrlNormalized = str_replace(['127.0.0.1', 'localhost'], 'LOCALHOST', strtolower($currentUrl));
        $baseUrlNormalized = str_replace(['127.0.0.1', 'localhost'], 'LOCALHOST', strtolower($baseUrl));
        
        // Extract ports for comparison
        $currentPort = parse_url($currentUrl, PHP_URL_PORT) ?? 80;
        $apiPort = parse_url($baseUrl, PHP_URL_PORT) ?? 80;
        
        // Warn if trying to call the same server on the same port (but allow same server different port)
        if ($currentPort === $apiPort && str_starts_with($baseUrlNormalized, $currentUrlNormalized . '/api')) {
            \Log::info(
                "API_BASE_URL points to the same server and port as frontend. " .
                "This is allowed if API routes are in the same project."
            );
        }
        
        $this->client = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withoutVerifying() // Disable SSL verification untuk staging
            ->timeout((int) config('services.backend.timeout', 10))
            ->connectTimeout(5);
    }

    /**
     * Perform a GET request to the backend API.
     */
    public function get(string $endpoint, array $query = [], ?string $token = null): array
    {
        return $this->request('get', $endpoint, $query, $token);
    }

    /**
     * Perform a POST request to the backend API.
     */
    public function post(string $endpoint, array $payload = [], ?string $token = null): array
    {
        return $this->request('post', $endpoint, $payload, $token);
    }

    /**
     * Perform a PUT request to the backend API.
     */
    public function put(string $endpoint, array $payload = [], ?string $token = null): array
    {
        return $this->request('put', $endpoint, $payload, $token);
    }

    /**
     * Perform a DELETE request to the backend API.
     */
    public function delete(string $endpoint, array $payload = [], ?string $token = null): array
    {
        return $this->request('delete', $endpoint, $payload, $token);
    }

    /**
     * Return a cloned instance that carries the given bearer token.
     */
    public function withToken(?string $token): self
    {
        $clone = clone $this;

        if ($token) {
            $clone->client = clone $this->client;
            $clone->client = $clone->client->withToken($token);
        }

        return $clone;
    }

    /**
     * Base request handler.
     *
     * @throws BackendApiException
     */
    protected function request(string $method, string $endpoint, array $data = [], ?string $token = null): array
    {
        $endpoint = '/' . ltrim($endpoint, '/');
        $baseUrl = rtrim(config('services.backend.base_url'), '/');
        $fullUrl = $baseUrl . $endpoint;

        $client = clone $this->client;

        if ($token) {
            $client = $client->withToken($token);
        }

        try {
            /** @var Response $response */
            $response = match (strtolower($method)) {
                'get' => $client->get($endpoint, $data),
                'post' => $client->post($endpoint, $data),
                'put' => $client->put($endpoint, $data),
                'delete' => $client->delete($endpoint, $data),
                default => throw new BackendApiException("Unsupported method [{$method}]")
            };
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $message = "Tidak dapat terhubung ke backend API di {$fullUrl}. ";
            $message .= "Pastikan backend server sedang berjalan dan API_BASE_URL di .env sudah benar.";
            
            throw new BackendApiException($message, null, $e);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Handle timeout specifically
            if (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'timed out')) {
                throw new BackendApiException(
                    "Backend API tidak merespons (timeout). " .
                    "Pastikan backend server berjalan di {$fullUrl}",
                    null,
                    $e
                );
            }
            
            throw new BackendApiException(
                "Backend API request failed: " . $e->getMessage(),
                null,
                $e
            );
        } catch (\Exception $e) {
            throw new BackendApiException(
                "Backend API request failed: " . $e->getMessage(),
                null,
                $e
            );
        }

        if ($response->failed()) {
            $message = $response->json('message') ?? 'Backend API request failed';
            $status = $response->status();
            
            // Log detailed error information
            \Log::error('Backend API Error', [
                'endpoint' => $endpoint,
                'method' => $method,
                'status' => $status,
                'message' => $message,
                'response_body' => $response->body(),
                'response_json' => $response->json(),
            ]);
            
            throw new BackendApiException(
                "Backend API returned error ({$status}): {$message}",
                $response
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Build an absolute asset URL from the backend.
     */
    public function assetUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $base = rtrim(config('services.backend.asset_base_url', config('services.backend.base_url')), '/');

        return $base . '/' . ltrim($path, '/');
    }
}

