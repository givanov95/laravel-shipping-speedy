<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy;

use Brick\Money\Money;
use Givanov95\Shipping\Contracts\DeliveryMethod;
use Givanov95\Shipping\Contracts\Parcel;
use Givanov95\Shipping\Contracts\PlaceDirectory;
use Givanov95\Shipping\Destination;
use Givanov95\Shipping\DestinationType;
use Givanov95\Shipping\Place;
use Givanov95\Shipping\Point;
use Givanov95\Shipping\Street;

/**
 * Speedy behind the contract: to one of its offices, or to an address in a site it knows by id.
 */
class SpeedyMethod implements DeliveryMethod, PlaceDirectory
{
    private const SEARCH_LIMIT = 20;

    public function __construct(private readonly SpeedyService $speedy)
    {
    }

    public function key(): string
    {
        return 'speedy';
    }

    public function destinationTypes(): array
    {
        return [DestinationType::Office, DestinationType::Address];
    }

    public function rules(DestinationType $type): array
    {
        $place = [
            'place_id' => ['required', 'integer'],
            'city'     => ['required', 'string', 'max:255'],
        ];

        if ($type === DestinationType::Office) {
            return [
                ...$place,
                // Speedy knows an office by a number.
                'point_code' => ['required', 'integer'],
                'point_name' => ['required', 'string', 'max:255'],
            ];
        }

        return [
            ...$place,
            'postal_code'   => ['nullable', 'string', 'max:20'],
            'street_id'     => ['nullable', 'integer'],
            'street'        => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'note'          => ['nullable', 'string', 'max:255'],
        ];
    }

    public function destination(DestinationType $type, array $choice): Destination
    {
        $streetId = $choice['street_id'] ?? null;

        return new Destination(
            method: $this->key(),
            type: $type,
            countryCode: $choice['country_code'],
            city: $choice['city'],
            postalCode: $choice['postal_code'] ?? null,
            street: $choice['street'] ?? null,
            streetNumber: $choice['street_number'] ?? null,
            note: $choice['note'] ?? null,
            pointCode: isset($choice['point_code']) ? (string) $choice['point_code'] : null,
            pointName: $choice['point_name'] ?? null,
            courierData: [
                'site_id' => (int) $choice['place_id'],
                // A street typed by the customer has no id.
                ...($streetId === null ? [] : ['street_id' => (int) $streetId]),
            ],
        );
    }

    /**
     * Speedy prices the delivery itself, in the currency of its answer.
     */
    public function price(Destination $destination, Parcel $parcel): ?Money
    {
        $shipment = $destination->type === DestinationType::Office
            ? $this->speedy->getOfficeShipment((int) $destination->pointCode)
            : $this->speedy->getAddressShipment(
                receiverSiteId: (int) $destination->courierData['site_id'],
                receiverStreetId: $destination->courierData['street_id'] ?? null,
                receiverStreetName: $destination->street,
                receiverStreetNo: (string) $destination->streetNumber,
            );

        return $shipment->getPrice();
    }

    /**
     * Speedy finds its sites by name, so there is nothing to ask it before the customer types.
     */
    public function places(string $search): array
    {
        $search = trim($search);

        if ($search === '') {
            return [];
        }

        $places = [];

        foreach (array_slice($this->speedy->getSites($search), 0, self::SEARCH_LIMIT) as $site) {
            if (isset($site->id, $site->name)) {
                $places[] = new Place((string) $site->id, (string) $site->name, $site->postCode ?? null);
            }
        }

        return $places;
    }

    public function points(string $placeId, DestinationType $type): array
    {
        if ($type !== DestinationType::Office) {
            return [];
        }

        $points = [];

        foreach ($this->speedy->getOffices((int) $placeId) as $office) {
            if (isset($office->id, $office->name)) {
                $points[] = new Point((string) $office->id, (string) $office->name);
            }
        }

        return $points;
    }

    public function streets(string $placeId, string $search): array
    {
        $search = trim($search);
        $streets = [];

        foreach (array_slice($this->speedy->getStreets((int) $placeId, $search === '' ? null : $search), 0, self::SEARCH_LIMIT) as $street) {
            if (isset($street->name)) {
                $streets[] = new Street((string) $street->name, isset($street->id) ? (string) $street->id : null);
            }
        }

        return $streets;
    }
}
