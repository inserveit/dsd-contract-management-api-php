<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Exception;

use Inserve\DSDContractManagementAPI\Models\ApiError;
use RuntimeException;
use Throwable;

final class DSDContractManagementAPIException extends RuntimeException
{
    /**
     * @param string         $message
     * @param int            $statusCode
     * @param ApiError|null  $errorResponse
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message,
        int $statusCode = 0,
        private readonly ?ApiError $errorResponse = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    /**
     * @return ApiError|null
     */
    public function getErrorResponse(): ?ApiError
    {
        return $this->errorResponse;
    }
}
