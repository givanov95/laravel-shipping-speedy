<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Requests;

class FindSiteRequest extends BaseSpeedyRequest
{
    public ?string $countryId;

    public ?string $name;

    public ?string $postCode;

    public ?string $type;

    public function __construct(?string $name = null, ?string $postCode = null, ?string $countryId = '100', ?string $type = null)
    {
        $this->name = $name;
        $this->postCode = $postCode;
        $this->countryId = $countryId;
        $this->type = $type;
    }

    public function endpoint(): string
    {
        return '/location/site/';
    }
}
