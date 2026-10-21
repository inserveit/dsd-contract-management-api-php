<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class ApiError
{
    /** @param array<string, string>|null $errors */
    public function __construct(
        public string  $title,
        public int     $status,
        public ?string $detail = null,
        /** @var array<string, string>|null */
        public ?array  $errors = null,
    ) {
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /** @return array<string, string>|null */
    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
