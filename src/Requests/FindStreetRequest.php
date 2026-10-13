<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Requests;

class FindStreetRequest extends BaseSpeedyRequest
{
    public ?int $siteId;

    public ?string $name;

    public ?string $type;

    public function __construct(?int $siteId = null, ?string $name = null, ?string $type = null)
    {
        $this->siteId = $siteId;
        $this->name = $name;
        $this->type = $type;
    }

    public function endpoint(): string
    {
        return '/location/street/';
    }
}
