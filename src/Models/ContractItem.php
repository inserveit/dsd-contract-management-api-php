<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

use Inserve\DSDContractManagementAPI\Enums\ContractStatus;

final readonly class ContractItem
{
    /**
     * @param string          $type
     * @param ContractStatus  $status
     * @param ProductContract $product
     * @param string|null     $subscriptionId
     */
    public function __construct(
        public string          $type,
        public ContractStatus  $status,
        public ProductContract $product,
        public ?string         $subscriptionId = null,
    ) {
    }

    /**
     * @return string|null
     */
    public function getSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return ContractStatus
     */
    public function getStatus(): ContractStatus
    {
        return $this->status;
    }

    /**
     * @return ProductContract
     */
    public function getProduct(): ProductContract
    {
        return $this->product;
    }
}
