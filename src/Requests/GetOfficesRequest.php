<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Requests;

class GetOfficesRequest extends BaseSpeedyRequest
{
    public ?string $countryId;

    public ?int $siteId;

    public ?string $name;

    public function __construct(?int $siteId = null, ?string $name = null, ?string $countryId = '100')
    {
        $this->siteId = $siteId;
        $this->name = $name;
        $this->countryId = $countryId;
    }

    public function endpoint(): string
    {
        return '/location/office/';
    }
}
