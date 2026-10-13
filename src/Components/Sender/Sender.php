<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Components\Sender;

use Givanov95\Shipping\Speedy\Components\Address\Address;
use Givanov95\Shipping\Speedy\Components\BaseComponent;

class Sender extends BaseComponent
{
    public ?int $clientId;

    public ?int $dropoffOfficeId;

    public ?Address $pickupAddress;

    public ?string $phone1;

    public ?string $contactName;

    public ?string $email;

    public function __construct(
        ?int $clientId = null,
        ?int $dropoffOfficeId = null,
        ?Address $pickupAddress = null,
        ?string $phone1 = null,
        ?string $contactName = null,
        ?string $email = null,
    ) {
        $this->clientId = $clientId;
        $this->dropoffOfficeId = $dropoffOfficeId;
        $this->pickupAddress = $pickupAddress;
        $this->phone1 = $phone1;
        $this->contactName = $contactName;
        $this->email = $email;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if (isset($data['phone1']) && is_string($data['phone1'])) {
            $data['phone1'] = ['number' => $data['phone1']];
        }

        return $data;
    }

    public function validate(): bool
    {
        return $this->clientId !== null || $this->pickupAddress !== null || $this->dropoffOfficeId !== null;
    }
}
