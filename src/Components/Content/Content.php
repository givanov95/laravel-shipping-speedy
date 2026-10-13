<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Components\Content;

use Givanov95\Shipping\Speedy\Components\BaseComponent;

class Content extends BaseComponent
{
    public ?int $parcelsCount;

    public ?float $totalWeight;

    public ?string $contents;

    public ?string $package;

    public ?bool $documents;

    public ?bool $palletized;

    public function __construct(
        ?int $parcelsCount = 1,
        ?float $totalWeight = null,
        ?string $contents = null,
        ?string $package = null,
        ?bool $documents = false,
        ?bool $palletized = false,
    ) {
        $this->parcelsCount = $parcelsCount;
        $this->totalWeight = $totalWeight;
        $this->contents = $contents;
        $this->package = $package;
        $this->documents = $documents;
        $this->palletized = $palletized;
    }

    public function validate(): bool
    {
        return $this->parcelsCount !== null && $this->totalWeight !== null;
    }
}
