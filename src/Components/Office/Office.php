<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Components\Office;

use Givanov95\Shipping\Speedy\Components\BaseComponent;

class Office extends BaseComponent
{
    public ?int $id;

    public ?string $name;

    public ?string $nameEn;

    public ?int $siteId;

    public ?string $address;

    public ?string $addressEn;

    public ?array $workingTimeFrom;

    public ?array $workingTimeTo;

    public ?array $workingTimeHalfFrom;

    public ?array $workingTimeHalfTo;

    public ?bool $sameDayDepartureCutoff;

    public ?bool $palletOffice;

    public ?int $type;

    public ?string $nearbyOfficeId;

    public ?int $cargoTypesAllowed;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $nameEn = null,
        ?int $siteId = null,
        ?string $address = null,
        ?string $addressEn = null,
        ?array $workingTimeFrom = null,
        ?array $workingTimeTo = null,
        ?array $workingTimeHalfFrom = null,
        ?array $workingTimeHalfTo = null,
        ?bool $sameDayDepartureCutoff = null,
        ?bool $palletOffice = null,
        ?int $type = null,
        ?string $nearbyOfficeId = null,
        ?int $cargoTypesAllowed = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->nameEn = $nameEn;
        $this->siteId = $siteId;
        $this->address = $address;
        $this->addressEn = $addressEn;
        $this->workingTimeFrom = $workingTimeFrom;
        $this->workingTimeTo = $workingTimeTo;
        $this->workingTimeHalfFrom = $workingTimeHalfFrom;
        $this->workingTimeHalfTo = $workingTimeHalfTo;
        $this->sameDayDepartureCutoff = $sameDayDepartureCutoff;
        $this->palletOffice = $palletOffice;
        $this->type = $type;
        $this->nearbyOfficeId = $nearbyOfficeId;
        $this->cargoTypesAllowed = $cargoTypesAllowed;
    }

    public function validate(): bool
    {
        return $this->id !== null;
    }
}
