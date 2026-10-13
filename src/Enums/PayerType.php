<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Enums;

enum PayerType: string
{
    case Sender = 'SENDER';
    case Recipient = 'RECIPIENT';
    case ThirdParty = 'THIRD_PARTY';
}
