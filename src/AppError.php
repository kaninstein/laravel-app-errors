<?php

namespace Kaninstein\LaravelAppErrors;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class AppError
{
    /**
     * @param  array<string, array<int, string>>|null  $errors
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>|null  $debug
     */
    public function __construct(
        public string $code,
        public string $message,
        public int $httpStatus = Response::HTTP_BAD_REQUEST,
        public ?string $requestId = null,
        public ?array $errors = null,
        public array $meta = [],
        public ?array $debug = null,
    ) {}

    public static function validation(?array $errors, ?string $requestId = null, ?array $debug = null): self
    {
        return new self(
            code: 'validation.failed',
            message: 'Revise os campos e tente novamente.',
            httpStatus: Response::HTTP_UNPROCESSABLE_ENTITY,
            requestId: $requestId,
            errors: $errors,
            debug: $debug,
        );
    }

    public static function unauthenticated(?string $requestId = null): self
    {
        return new self('auth.unauthenticated', 'Faça login para continuar.', Response::HTTP_UNAUTHORIZED, $requestId);
    }

    public static function forbidden(?string $requestId = null): self
    {
        return new self('auth.forbidden', 'Você não tem permissão para esta ação.', Response::HTTP_FORBIDDEN, $requestId);
    }

    public static function notFound(?string $requestId = null): self
    {
        return new self('resource.not_found', 'Recurso não encontrado.', Response::HTTP_NOT_FOUND, $requestId);
    }

    public static function conflict(string $message, ?string $requestId = null, array $meta = [], ?array $debug = null): self
    {
        return new self('business.conflict', $message, Response::HTTP_CONFLICT, $requestId, null, $meta, $debug);
    }

    public static function preconditionFailed(string $message, ?string $requestId = null, array $meta = [], ?array $debug = null): self
    {
        return new self('business.precondition_failed', $message, Response::HTTP_PRECONDITION_FAILED, $requestId, null, $meta, $debug);
    }

    public static function gatewayUnavailable(string $message, ?string $requestId = null, array $meta = [], ?array $debug = null): self
    {
        return new self('payments.gateway_unavailable', $message, Response::HTTP_SERVICE_UNAVAILABLE, $requestId, null, $meta, $debug);
    }

    public static function unexpected(?string $requestId = null, ?array $debug = null, string $publicMessage = 'Ocorreu um erro inesperado. Tente novamente.'): self
    {
        return new self('app.unexpected', $publicMessage, Response::HTTP_INTERNAL_SERVER_ERROR, $requestId, null, [], $debug);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $includeDebug = false, bool $appendRequestIdToMessage = false): array
    {
        $message = $this->message;
        if ($appendRequestIdToMessage && is_string($this->requestId) && $this->requestId !== '') {
            $message = "{$message} (Request: {$this->requestId})";
        }

        $payload = [
            'success' => false,
            'code' => $this->code,
            'message' => $message,
            'request_id' => $this->requestId,
        ];

        if (is_array($this->errors) && $this->errors !== []) {
            $payload['errors'] = $this->errors;
        }

        if ($this->meta !== []) {
            $payload['meta'] = $this->meta;
        }

        if ($includeDebug && is_array($this->debug) && $this->debug !== []) {
            $payload['debug'] = $this->debug;
        }

        return $payload;
    }

    public function toJsonResponse(bool $includeDebug = false, bool $appendRequestIdToMessage = false, ?int $forceStatus = null): JsonResponse
    {
        $status = $forceStatus ?? $this->httpStatus;

        return response()->json(
            $this->toArray($includeDebug, $appendRequestIdToMessage),
            $status,
            array_filter([
                'X-Request-Id' => $this->requestId,
            ], static fn ($v) => $v !== null && $v !== ''),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}

