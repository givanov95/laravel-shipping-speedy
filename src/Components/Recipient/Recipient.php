<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Components\Recipient;

use Givanov95\Shipping\Speedy\Components\Address\Address;
use Givanov95\Shipping\Speedy\Components\BaseComponent;

class Recipient extends BaseComponent
{
    public ?bool $privatePerson;

    public ?int $pickupOfficeId;

    public ?Address $address;

    public ?string $phone1;

    public ?string $contactName;

    public ?string $clientName;

    public ?string $email;

    public function __construct(
        ?bool $privatePerson = true,
        ?int $pickupOfficeId = null,
        ?Address $address = null,
        ?string $phone1 = null,
        ?string $contactName = null,
        ?string $clientName = null,
        ?string $email = null,
    ) {
        $this->privatePerson = $privatePerson;
        $this->pickupOfficeId = $pickupOfficeId;
        $this->address = $address;
        $this->phone1 = $phone1;
        $this->contactName = $contactName;
        $this->clientName = $clientName;
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
        return $this->pickupOfficeId !== null || $this->address !== null;
    }
}
