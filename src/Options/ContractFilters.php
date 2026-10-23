<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Options;

use Inserve\DSDContractManagementAPI\Enums\ContractStatus;
use InvalidArgumentException;

final readonly class ContractFilters
{
    /**
     * @param ContractStatus|string|null $status
     * @param int|null    $clientId
     * @param bool|null   $ownUse
     * @param string|null $saleReference
     * @param string|null $renewalFrom
     * @param string|null $renewalTo
     * @param string|null $createdFrom
     * @param string|null $createdTo
     * @param string|null $updatedFrom
     * @param string|null $updatedTo
     * @param string|null $productName
     * @param string|null $productBrand
     * @param string|null $sku
     */
    public function __construct(
        public ContractStatus|string|null $status = null,
        public ?int    $clientId = null,
        public ?bool   $ownUse = null,
        public ?string $saleReference = null,
        public ?string $renewalFrom = null,
        public ?string $renewalTo = null,
        public ?string $createdFrom = null,
        public ?string $createdTo = null,
        public ?string $updatedFrom = null,
        public ?string $updatedTo = null,
        public ?string $productName = null,
        public ?string $productBrand = null,
        public ?string $sku = null,
    ) {
        if ($clientId !== null && $clientId < 1) {
            throw new InvalidArgumentException('Client ID must be positive.');
        }
        if ($clientId !== null && $ownUse === true) {
            throw new InvalidArgumentException('clientId cannot be combined with ownUse=true.');
        }
        if (is_string($status) && ContractStatus::tryFrom($status) === null) {
            throw new InvalidArgumentException('Invalid contract status.');
        }
    }

    /**
     * @return (bool|int|string)[] Dates must use the formats documented by DSD.
     *
     * @psalm-return array<string, bool|int|string>
     */
    public function toQuery(): array
    {
        $query = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== null) {
                $query[$key] = match (true) {
                    $value instanceof ContractStatus => $value->value,
                    is_bool($value) => $value ? 'true' : 'false',
                    default => $value,
                };
            }
        }

        return $query;
    }
}
