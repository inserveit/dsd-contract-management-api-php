<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class Contract
{
    /**
     * @param list<ContractItem> $items
     */
    public function __construct(
        public int            $id,
        public Account        $account,
        public string         $status,
        public string         $invoicingPeriod,
        public string         $created,
        /** @var list<ContractItem> */
        public array          $items,
        public ContractTotals $totals,
        public ?Client        $client = null,
        public ?string        $saleReference = null,
        public ?string        $activationDate = null,
        public ?string        $commitmentEndDate = null,
        public ?string        $renewalDate = null,
        public ?string        $commitmentTerm = null,
        public ?string        $updated = null,
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
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getSaleReference(): ?string
    {
        return $this->saleReference;
    }

    /**
     * @return string|null
     */
    public function getActivationDate(): ?string
    {
        return $this->activationDate;
    }

    /**
     * @return string|null
     */
    public function getCommitmentEndDate(): ?string
    {
        return $this->commitmentEndDate;
    }

    /**
     * @return string|null
     */
    public function getRenewalDate(): ?string
    {
        return $this->renewalDate;
    }

    /**
     * @return string|null
     */
    public function getCommitmentTerm(): ?string
    {
        return $this->commitmentTerm;
    }

    /**
     * @return string
     */
    public function getInvoicingPeriod(): string
    {
        return $this->invoicingPeriod;
    }

    /**
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created;
    }

    /**
     * @return string|null
     */
    public function getUpdated(): ?string
    {
        return $this->updated;
    }

    /** @return list<ContractItem> */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return ContractTotals
     */
    public function getTotals(): ContractTotals
    {
        return $this->totals;
    }
}
