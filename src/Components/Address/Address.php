<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Components\Address;

use Givanov95\Shipping\Speedy\Components\BaseComponent;

class Address extends BaseComponent
{
    public ?string $countryId;

    public ?int $siteId;

    public ?string $siteType;

    public ?string $siteName;

    public ?string $postCode;

    public ?int $streetId;

    public ?string $streetType;

    public ?string $streetName;

    public ?string $streetNo;

    public ?string $blockNo;

    public ?string $entranceNo;

    public ?string $floorNo;

    public ?string $apartmentNo;

    public ?string $addressNote;

    public ?string $addressLine1;

    public ?string $addressLine2;

    public ?float $x;

    public ?float $y;

    public function __construct(
        ?string $countryId = '100',
        ?int $siteId = null,
        ?string $siteType = null,
        ?string $siteName = null,
        ?string $postCode = null,
        ?int $streetId = null,
        ?string $streetType = null,
        ?string $streetName = null,
        ?string $streetNo = null,
        ?string $blockNo = null,
        ?string $entranceNo = null,
        ?string $floorNo = null,
        ?string $apartmentNo = null,
        ?string $addressNote = null,
        ?string $addressLine1 = null,
        ?string $addressLine2 = null,
        ?float $x = null,
        ?float $y = null,
    ) {
        $this->countryId = $countryId;
        $this->siteId = $siteId;
        $this->siteType = $siteType;
        $this->siteName = $siteName;
        $this->postCode = $postCode;
        $this->streetId = $streetId;
        $this->streetType = $streetType;
        $this->streetName = $streetName;
        $this->streetNo = $streetNo;
        $this->blockNo = $blockNo;
        $this->entranceNo = $entranceNo;
        $this->floorNo = $floorNo;
        $this->apartmentNo = $apartmentNo;
        $this->addressNote = $addressNote;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->x = $x;
        $this->y = $y;
    }

    public function validate(): bool
    {
        return $this->siteId !== null;
    }
}
