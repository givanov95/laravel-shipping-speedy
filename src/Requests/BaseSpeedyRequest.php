<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Requests;

use Givanov95\TypedHttp\Enums\HttpMethod;
use Givanov95\TypedHttp\Request;

/**
 * Every Speedy call is a JSON POST. The credentials (userName, password, language) are not properties of the
 * requests: SpeedyConnector::defaultBody() adds them to the body of each one.
 */
abstract class BaseSpeedyRequest extends Request
{
    public function method(): HttpMethod
    {
        return HttpMethod::POST;
    }
}
