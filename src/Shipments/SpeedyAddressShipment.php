<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Shipments;

use Brick\Money\Money;
use Givanov95\Shipping\Speedy\Components\Address\Address;
use Givanov95\Shipping\Speedy\Components\Content\Content;
use Givanov95\Shipping\Speedy\Components\Recipient\Recipient;
use Givanov95\Shipping\Speedy\Components\Sender\Sender;
use Givanov95\Shipping\Speedy\Enums\PayerType;
use Givanov95\Shipping\Speedy\Requests\CalculationRequest;
use Givanov95\Shipping\Speedy\Speedy;

class SpeedyAddressShipment
{
    public function __construct(
        private readonly ?int $senderClientId,
        private readonly ?int $senderOfficeId,
        private readonly string $senderName,
        private readonly string $senderPhone,
        private readonly int $receiverSiteId,
        private readonly ?int $receiverStreetId,
        private readonly ?string $receiverStreetName,
        private readonly string $receiverStreetNo,
        private readonly string $receiverName,
        private readonly string $receiverPhone,
        private readonly float $packageWeight,
        private readonly string $description,
        private readonly int $serviceId = 505,
        private readonly string $countryId = '100',
    ) {
    }

    public function getCalculationRequest(): CalculationRequest
    {
        $sender = new Sender(
            clientId: $this->senderClientId,
            dropoffOfficeId: $this->senderOfficeId,
            phone1: $this->senderPhone,
            contactName: $this->senderName,
        );

        $receiverAddress = new Address(
            countryId: $this->countryId,
            siteId: $this->receiverSiteId,
            streetId: $this->receiverStreetId,
            streetName: $this->receiverStreetName,
            streetNo: $this->receiverStreetNo,
        );

        $recipient = new Recipient(
            privatePerson: true,
            address: $receiverAddress,
            phone1: $this->receiverPhone,
            contactName: $this->receiverName,
        );

        $content = new Content(
            parcelsCount: 1,
            totalWeight: $this->packageWeight,
            contents: $this->description,
            package: 'BOX',
        );

        return new CalculationRequest(
            sender: $sender,
            recipient: $recipient,
            content: $content,
            payerType: PayerType::Recipient,
            serviceId: $this->serviceId,
        );
    }

    public function getPrice(): ?Money
    {
        $response = (new Speedy())->calculate($this->getCalculationRequest());

        $calculation = $response?->calculations[0] ?? null;

        if (! $calculation) {
            return null;
        }

        return Money::of((string) $calculation->price->total, $calculation->price->currency ?? 'BGN');
    }

    public function toArray(): array
    {
        $price = $this->getPrice();

        if (! $price) {
            return [];
        }

        return [
            'amount'   => $price->getAmount(),
            'currency' => $price->getCurrency()->getCurrencyCode(),
        ];
    }
}
