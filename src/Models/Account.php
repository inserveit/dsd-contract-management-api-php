<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class Account
{
    /**
     * @param int    $id
     * @param string $username
     * @param bool   $isSubaccount
     */
    public function __construct(
        public int    $id,
        public string $username,
        public bool   $isSubaccount,
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
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return bool
     */
    public function getIsSubaccount(): bool
    {
        return $this->isSubaccount;
    }
}
