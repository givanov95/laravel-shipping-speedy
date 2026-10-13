<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Components\Site;

use Givanov95\Shipping\Speedy\Components\BaseComponent;

class Site extends BaseComponent
{
    public ?int $id;

    public ?string $countryId;

    public ?string $postCode;

    public ?string $name;

    public ?string $nameEn;

    public ?string $type;

    public ?string $typeEn;

    public ?string $municipality;

    public ?string $municipalityEn;

    public ?string $region;

    public ?string $regionEn;

    public ?float $x;

    public ?float $y;

    public ?bool $servingDays;

    public ?array $addressNomenclature;

    public function __construct(
        ?int $id = null,
        ?string $countryId = null,
        ?string $postCode = null,
        ?string $name = null,
        ?string $nameEn = null,
        ?string $type = null,
        ?string $typeEn = null,
        ?string $municipality = null,
        ?string $municipalityEn = null,
        ?string $region = null,
        ?string $regionEn = null,
        ?float $x = null,
        ?float $y = null,
        ?bool $servingDays = null,
        ?array $addressNomenclature = null,
    ) {
        $this->id = $id;
        $this->countryId = $countryId;
        $this->postCode = $postCode;
        $this->name = $name;
        $this->nameEn = $nameEn;
        $this->type = $type;
        $this->typeEn = $typeEn;
        $this->municipality = $municipality;
        $this->municipalityEn = $municipalityEn;
        $this->region = $region;
        $this->regionEn = $regionEn;
        $this->x = $x;
        $this->y = $y;
        $this->servingDays = $servingDays;
        $this->addressNomenclature = $addressNomenclature;
    }

    public function validate(): bool
    {
        return $this->id !== null;
    }
}
