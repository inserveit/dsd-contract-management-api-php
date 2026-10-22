<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class EndClient
{
    /**
     * @param int         $id
     * @param int         $userId
     * @param bool        $active
     * @param string      $name
     * @param string      $created
     * @param string      $updated
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $company
     * @param string|null $address
     * @param string|null $houseNr
     * @param string|null $postalCode
     * @param string|null $city
     * @param string|null $country
     * @param string|null $province
     * @param string|null $phone
     * @param string|null $email
     * @param string|null $website
     * @param string|null $numberCoc
     * @param string|null $numberVat
     */
    public function __construct(
        public int $id,
        public int $userId,
        public bool $active,
        public string $name,
        public string $created,
        public string $updated,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $company = null,
        public ?string $address = null,
        public ?string $houseNr = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $province = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $website = null,
        public ?string $numberCoc = null,
        public ?string $numberVat = null,
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
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getUpdated(): string
    {
        return $this->updated;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
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
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return string|null
     */
    public function getHouseNr(): ?string
    {
        return $this->houseNr;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getProvince(): ?string
    {
        return $this->province;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @return string|null
     */
    public function getNumberCoc(): ?string
    {
        return $this->numberCoc;
    }

    /**
     * @return string|null
     */
    public function getNumberVat(): ?string
    {
        return $this->numberVat;
    }
}
