<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class ContractItem
{
    /**
     * @param string          $type
     * @param string          $status
     * @param ProductContract $product
     * @param string|null     $subscriptionId
     */
    public function __construct(
        public string          $type,
        public string          $status,
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
     * @return string
     */
    public function getStatus(): string
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
