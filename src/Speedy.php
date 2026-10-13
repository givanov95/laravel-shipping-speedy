<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy;

use Givanov95\Shipping\Speedy\Requests\CalculationRequest;
use Givanov95\Shipping\Speedy\Requests\CreateShipmentRequest;
use Givanov95\Shipping\Speedy\Requests\FindSiteRequest;
use Givanov95\Shipping\Speedy\Requests\FindStreetRequest;
use Givanov95\Shipping\Speedy\Requests\GetOfficesRequest;
use Givanov95\TypedHttp\Request;

/**
 * Sends the Speedy requests and returns the decoded response (stdClass). Failures raise
 * Givanov95\TypedHttp\Exceptions\RequestException (4xx/5xx) or ConnectionException (no answer).
 */
class Speedy
{
    public function __construct(private readonly ?SpeedyConnector $connector = null)
    {
    }

    public function findSites(FindSiteRequest $request)
    {
        return $this->send($request);
    }

    public function getOffices(GetOfficesRequest $request)
    {
        return $this->send($request);
    }

    public function findStreets(FindStreetRequest $request)
    {
        return $this->send($request);
    }

    public function calculate(CalculationRequest $request)
    {
        return $this->send($request);
    }

    public function createShipment(CreateShipmentRequest $request)
    {
        return $this->send($request);
    }

    private function send(Request $request): mixed
    {
        return ($this->connector ?? new SpeedyConnector())->send($request)->object();
    }
}
