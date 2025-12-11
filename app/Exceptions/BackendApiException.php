<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class BackendApiException extends Exception
{
    protected ?Response $response;

    public function __construct(string $message = "", ?Response $response = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getStatusCode(): ?int
    {
        return $this->response?->status();
    }

    public function getResponseData(): ?array
    {
        return $this->response?->json();
    }
}