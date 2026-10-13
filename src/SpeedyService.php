<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy;

use Exception;
use Givanov95\Shipping\Speedy\Requests\FindSiteRequest;
use Givanov95\Shipping\Speedy\Requests\FindStreetRequest;
use Givanov95\Shipping\Speedy\Requests\GetOfficesRequest;
use Givanov95\Shipping\Speedy\Shipments\SpeedyAddressShipment;
use Givanov95\Shipping\Speedy\Shipments\SpeedyOfficeShipment;
use Illuminate\Support\Facades\Cache;

class SpeedyService
{
    public function getOfficeShipment(int $receiverOfficeId): SpeedyOfficeShipment
    {
        return new SpeedyOfficeShipment(
            senderClientId: $this->intConfig('speedy.sender.client_id'),
            senderOfficeId: $this->intConfig('speedy.sender.office_id'),
            senderName: (string) config('speedy.sender.name'),
            senderPhone: (string) config('speedy.sender.phone'),
            receiverOfficeId: $receiverOfficeId,
            receiverName: 'John Doe',
            receiverPhone: '+359888888888',
            packageWeight: (float) config('speedy.package_weight'),
            description: (string) config('speedy.package_description'),
        );
    }

    public function getAddressShipment(
        int $receiverSiteId,
        ?int $receiverStreetId,
        ?string $receiverStreetName,
        string $receiverStreetNo,
    ): SpeedyAddressShipment {
        return new SpeedyAddressShipment(
            senderClientId: $this->intConfig('speedy.sender.client_id'),
            senderOfficeId: $this->intConfig('speedy.sender.office_id'),
            senderName: (string) config('speedy.sender.name'),
            senderPhone: (string) config('speedy.sender.phone'),
            receiverSiteId: $receiverSiteId,
            receiverStreetId: $receiverStreetId,
            receiverStreetName: $receiverStreetName,
            receiverStreetNo: $receiverStreetNo,
            receiverName: 'John Doe',
            receiverPhone: '+359888888888',
            packageWeight: (float) config('speedy.package_weight'),
            description: (string) config('speedy.package_description'),
        );
    }

    public function getSites(?string $name = null): array
    {
        $cacheKey = 'speedy-sites-'.($name ?? 'all');

        $cached = Cache::get($cacheKey);

        if (! empty($cached)) {
            return $cached;
        }

        $speedy = new Speedy();
        $request = new FindSiteRequest(name: $name, countryId: '100');

        try {
            $sites = $speedy->findSites($request)->sites ?? [];
        } catch (Exception $e) {
            report($e);

            return [];
        }

        // Only a real answer is cached: a failed request must not hide the sites for a day.
        if (! empty($sites)) {
            Cache::put($cacheKey, $sites, now()->addDay());
        }

        return $sites;
    }

    public function getOffices(int $siteId): array
    {
        if (! $siteId) {
            return [];
        }

        $speedy = new Speedy();
        $request = new GetOfficesRequest(siteId: $siteId, countryId: '100');

        try {
            $response = $speedy->getOffices($request);

            return $response->offices ?? [];
        } catch (Exception $e) {
            report($e);

            return [];
        }
    }

    public function getStreets(int $siteId, ?string $name = null): array
    {
        if (! $siteId) {
            return [];
        }

        $speedy = new Speedy();
        $request = new FindStreetRequest(siteId: $siteId, name: $name);

        try {
            $response = $speedy->findStreets($request);

            return $response->streets ?? [];
        } catch (Exception $e) {
            report($e);

            return [];
        }
    }

    private function intConfig(string $key): ?int
    {
        $value = config($key);

        return $value === null || $value === '' ? null : (int) $value;
    }
}
