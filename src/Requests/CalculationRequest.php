<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Requests;

use Givanov95\Shipping\Speedy\Components\Content\Content;
use Givanov95\Shipping\Speedy\Components\Recipient\Recipient;
use Givanov95\Shipping\Speedy\Components\Sender\Sender;
use Givanov95\Shipping\Speedy\Enums\PayerType;

class CalculationRequest extends BaseSpeedyRequest
{
    public array $sender;

    public array $recipient;

    public array $service;

    public array $content;

    public array $payment;

    public function __construct(
        Sender $sender,
        Recipient $recipient,
        Content $content,
        PayerType $payerType = PayerType::Recipient,
        int $serviceId = 505,
    ) {
        $this->sender = $sender->toArray();
        $this->recipient = $recipient->toArray();
        $this->content = $content->toArray();
        $this->service = [
            'autoAdjustPickupDate' => true,
            'serviceIds'           => [$serviceId],
        ];
        $this->payment = [
            'courierServicePayer' => $payerType->value,
        ];
    }

    public function endpoint(): string
    {
        return '/calculate/';
    }
}
