<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class Client
{
    /**
     * @param int         $id
     * @param string|null $company
     * @param string|null $cocIdentifier
     */
    public function __construct(
        public int     $id,
        public ?string $company = null,
        public ?string $cocIdentifier = null,
    ) {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return string|null
     */
    public function getCocIdentifier(): ?string
    {
        return $this->cocIdentifier;
    }
}
