<?php

namespace Kaninstein\LaravelAppErrors\Http;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kaninstein\LaravelAppErrors\AppError;
use Kaninstein\LaravelAppErrors\AppErrorException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ExceptionMapper
{
    public function __construct(
        private readonly string $defaultPublicMessage,
    ) {}

    public function fromThrowable(Throwable $e, Request $request, bool $exposeDebug): AppError
    {
        $requestId = (string) ($request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id') ?? '');

        if ($e instanceof AppErrorException) {
            return new AppError(
                code: $e->appError->code,
                message: $e->appError->message,
                httpStatus: $e->appError->httpStatus,
                requestId: $requestId ?: $e->appError->requestId,
                errors: $e->appError->errors,
                meta: $e->appError->meta,
                debug: $exposeDebug ? ($e->appError->debug ?? ['exception' => get_class($e)]) : null,
            );
        }

        if ($e instanceof ValidationException) {
            return AppError::validation(
                errors: $e->errors(),
                requestId: $requestId,
                debug: $exposeDebug ? ['message' => $e->getMessage()] : null,
            );
        }

        if ($e instanceof AuthenticationException) {
            return AppError::unauthenticated($requestId);
        }

        if ($e instanceof AuthorizationException) {
            return AppError::forbidden($requestId);
        }

        if ($e instanceof ModelNotFoundException) {
            return AppError::notFound($requestId);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = match ($status) {
                401 => AppError::unauthenticated($requestId)->message,
                403 => AppError::forbidden($requestId)->message,
                404 => AppError::notFound($requestId)->message,
                default => $this->defaultPublicMessage,
            };

            return new AppError(
                code: "http.{$status}",
                message: $message,
                httpStatus: $status,
                requestId: $requestId,
                debug: $exposeDebug ? ['message' => $e->getMessage(), 'exception' => get_class($e)] : null,
            );
        }

        return AppError::unexpected(
            requestId: $requestId,
            debug: $exposeDebug ? [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ] : null,
            publicMessage: $this->defaultPublicMessage,
        );
    }
}

