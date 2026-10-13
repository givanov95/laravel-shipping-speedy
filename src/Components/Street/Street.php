<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Components\Street;

use Givanov95\Shipping\Speedy\Components\BaseComponent;

class Street extends BaseComponent
{
    public ?int $id;

    public ?int $siteId;

    public ?string $name;

    public ?string $nameEn;

    public ?string $type;

    public ?string $typeEn;

    public ?string $district;

    public ?string $districtEn;

    public function __construct(
        ?int $id = null,
        ?int $siteId = null,
        ?string $name = null,
        ?string $nameEn = null,
        ?string $type = null,
        ?string $typeEn = null,
        ?string $district = null,
        ?string $districtEn = null,
    ) {
        $this->id = $id;
        $this->siteId = $siteId;
        $this->name = $name;
        $this->nameEn = $nameEn;
        $this->type = $type;
        $this->typeEn = $typeEn;
        $this->district = $district;
        $this->districtEn = $districtEn;
    }

    public function validate(): bool
    {
        return $this->id !== null;
    }
}
