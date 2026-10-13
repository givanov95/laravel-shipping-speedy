<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Tests;

use Givanov95\Shipping\Contracts\PlaceDirectory;
use Givanov95\Shipping\DeliveryMethods;
use Givanov95\Shipping\DestinationType;
use Givanov95\Shipping\Place;
use Givanov95\Shipping\Point;
use Givanov95\Shipping\Speedy\SpeedyMethod;
use Givanov95\Shipping\Speedy\SpeedyService;
use Givanov95\Shipping\Speedy\Tests\Fixtures\Basket;
use Givanov95\Shipping\Street;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use ReflectionProperty;

/**
 * Speedy behind the delivery contract, the way a shop asks it: what the customer chooses,
 * the destination kept on the order, the price and the lists of the checkout form.
 */
final class SpeedyMethodTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $price
     */
    private function fakeSpeedy(array $price = ['total' => 6.2, 'currency' => 'BGN']): void
    {
        Http::fake([
            'speedy.test/v1/location/site/' => Http::response(['sites' => [
                ['id' => 68134, 'name' => 'СОФИЯ', 'postCode' => '1000'],
                ['id' => 68135, 'name' => 'СОФРОНИЕВО', 'postCode' => '3330'],
            ]]),
            'speedy.test/v1/location/office/' => Http::response(['offices' => [
                ['id' => 77, 'name' => 'СОФИЯ - ЦЕНТЪР'],
                ['id' => 78, 'name' => 'СОФИЯ - МЛАДОСТ'],
            ]]),
            'speedy.test/v1/location/street/' => Http::response(['streets' => [
                ['id' => 3109, 'name' => 'ВИТОША'],
            ]]),
            'speedy.test/v1/calculate/' => Http::response(['calculations' => [['price' => $price]]]),
        ]);
    }

    private function method(): SpeedyMethod
    {
        return $this->app->make(DeliveryMethods::class)->get('speedy');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function toOffice(array $overrides = []): array
    {
        return [
            'country_code' => 'BG',
            'place_id'     => '68134',
            'city'         => 'СОФИЯ',
            'point_code'   => '77',
            'point_name'   => 'СОФИЯ - ЦЕНТЪР',
            ...$overrides,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function toAddress(array $overrides = []): array
    {
        return [
            'country_code'  => 'BG',
            'place_id'      => '68134',
            'city'          => 'СОФИЯ',
            'postal_code'   => '1000',
            'street_id'     => '3109',
            'street'        => 'ВИТОША',
            'street_number' => '15',
            'note'          => 'Floor 2',
            ...$overrides,
        ];
    }

    /**
     * @param  array<string, mixed>  $choice
     * @return list<string> the fields the rules of the method refuse
     */
    private function refused(DestinationType $type, array $choice): array
    {
        return Validator::make($choice, $this->method()->rules($type))->errors()->keys();
    }

    public function test_the_package_registers_the_method_under_its_key(): void
    {
        $methods = $this->app->make(DeliveryMethods::class);

        $this->assertTrue($methods->has('speedy'));
        $this->assertSame(['speedy' => 'Speedy'], $methods->labels());
        $this->assertInstanceOf(SpeedyMethod::class, $methods->get('speedy'));
        $this->assertInstanceOf(PlaceDirectory::class, $methods->get('speedy'));
        $this->assertSame([DestinationType::Office, DestinationType::Address], $methods->get('speedy')->destinationTypes());
    }

    public function test_the_configuration_comes_with_the_package(): void
    {
        $this->assertSame('EN', config('speedy.language'));
        $this->assertSame('Parcel', config('speedy.package_description'));
        $this->assertSame(1, (int) config('speedy.package_weight'));
    }

    public function test_an_office_needs_the_site_and_the_office_by_its_number(): void
    {
        $this->assertSame([], $this->refused(DestinationType::Office, $this->toOffice()));
        $this->assertSame(['place_id', 'point_code'], $this->refused(DestinationType::Office, $this->toOffice(['place_id' => null, 'point_code' => null])));
        $this->assertSame(['point_code'], $this->refused(DestinationType::Office, $this->toOffice(['point_code' => 'not-a-number'])));
    }

    public function test_an_address_needs_the_street_and_the_number_but_not_the_id_of_the_street(): void
    {
        $this->assertSame([], $this->refused(DestinationType::Address, $this->toAddress()));
        $this->assertSame([], $this->refused(DestinationType::Address, $this->toAddress(['street_id' => null])));
        $this->assertSame(['street', 'street_number'], $this->refused(DestinationType::Address, $this->toAddress(['street' => null, 'street_number' => null])));
    }

    public function test_the_destination_of_an_office_keeps_the_site_speedy_knows(): void
    {
        $destination = $this->method()->destination(DestinationType::Office, $this->toOffice());

        $this->assertSame(
            [
                'method'        => 'speedy',
                'type'          => 'office',
                'country_code'  => 'BG',
                'city'          => 'СОФИЯ',
                'postal_code'   => null,
                'street'        => null,
                'street_number' => null,
                'note'          => null,
                'point_code'    => '77',
                'point_name'    => 'СОФИЯ - ЦЕНТЪР',
                'courier_data'  => ['site_id' => 68134],
            ],
            $destination->toArray(),
        );
    }

    public function test_the_destination_of_an_address_keeps_the_site_and_the_street_speedy_knows(): void
    {
        $destination = $this->method()->destination(DestinationType::Address, $this->toAddress());

        $this->assertSame(DestinationType::Address, $destination->type);
        $this->assertSame(['СОФИЯ', '1000', 'ВИТОША', '15', 'Floor 2', null], [
            $destination->city, $destination->postalCode, $destination->street, $destination->streetNumber, $destination->note, $destination->pointCode,
        ]);
        $this->assertSame(['site_id' => 68134, 'street_id' => 3109], $destination->courierData);
    }

    public function test_an_office_is_priced_by_speedy_for_the_client_of_the_sender(): void
    {
        $this->fakeSpeedy();

        $price = $this->method()->price($this->method()->destination(DestinationType::Office, $this->toOffice()), new Basket('BGN'));

        $this->assertSame(['6.20', 'BGN'], [(string) $price->getAmount(), $price->getCurrency()->getCurrencyCode()]);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://speedy.test/v1/calculate/'
            && $request->data()['recipient']['pickupOfficeId'] === 77
            && $request->data()['sender']['clientId'] === 555);
    }

    public function test_the_sender_and_the_weight_of_the_parcel_come_from_the_configuration(): void
    {
        config([
            'speedy.sender'         => ['client_id' => '555', 'office_id' => '12', 'name' => 'Speedy Shop', 'phone' => '+359222'],
            'speedy.package_weight' => '2.5',
        ]);
        $this->fakeSpeedy();

        (new SpeedyService())->getOfficeShipment(77)->getPrice();

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return $data['sender']['clientId'] === 555
                && $data['sender']['dropoffOfficeId'] === 12
                && $data['sender']['contactName'] === 'Speedy Shop'
                && $data['sender']['phone1']['number'] === '+359222'
                && $data['content']['totalWeight'] === 2.5;
        });
    }

    public function test_the_ids_of_a_sender_that_are_not_set_are_not_sent_as_zero(): void
    {
        config(['speedy.sender' => ['client_id' => '', 'office_id' => null, 'name' => 'S', 'phone' => '1']]);

        $shipment = (new SpeedyService())->getOfficeShipment(77);

        $this->assertNull((new ReflectionProperty($shipment, 'senderClientId'))->getValue($shipment));
        $this->assertNull((new ReflectionProperty($shipment, 'senderOfficeId'))->getValue($shipment));
    }

    public function test_an_address_is_priced_for_the_site_and_the_street_speedy_knows(): void
    {
        $this->fakeSpeedy(['total' => 8.4, 'currency' => 'BGN']);

        $price = $this->method()->price($this->method()->destination(DestinationType::Address, $this->toAddress()), new Basket('BGN'));

        $this->assertSame('8.40', (string) $price->getAmount());
        Http::assertSent(function (Request $request) {
            $address = $request->data()['recipient']['address'] ?? null;

            return $address !== null
                && $address['siteId'] === 68134
                && $address['streetId'] === 3109
                && $address['streetNo'] === '15';
        });
    }

    public function test_a_street_speedy_does_not_list_is_sent_by_its_name(): void
    {
        $this->fakeSpeedy();

        $destination = $this->method()->destination(DestinationType::Address, $this->toAddress(['street_id' => null, 'street' => 'Нова улица']));
        $this->method()->price($destination, new Basket('BGN'));

        $this->assertSame(['site_id' => 68134], $destination->courierData);
        Http::assertSent(function (Request $request) {
            $address = $request->data()['recipient']['address'] ?? null;

            return $address !== null && $address['streetName'] === 'Нова улица' && ! isset($address['streetId']);
        });
    }

    public function test_there_is_no_price_when_speedy_calculates_none(): void
    {
        Http::fake(['speedy.test/*' => Http::response(['calculations' => []])]);

        $this->assertNull($this->method()->price($this->method()->destination(DestinationType::Office, $this->toOffice()), new Basket('BGN')));
    }

    /**
     * Nothing is converted: the shop refuses a price in another currency than the one of the parcel.
     */
    public function test_the_price_is_in_the_currency_speedy_answers_in(): void
    {
        $this->fakeSpeedy(['total' => 3.2, 'currency' => 'EUR']);

        $price = $this->method()->price($this->method()->destination(DestinationType::Office, $this->toOffice()), new Basket('BGN'));

        $this->assertSame(['3.20', 'EUR'], [(string) $price->getAmount(), $price->getCurrency()->getCurrencyCode()]);
    }

    public function test_sites_are_found_by_the_text_the_customer_types(): void
    {
        $this->fakeSpeedy();

        $this->assertEquals(
            [new Place('68134', 'СОФИЯ', '1000'), new Place('68135', 'СОФРОНИЕВО', '3330')],
            $this->method()->places('соф'),
        );
        Http::assertSent(fn (Request $request) => $request->url() === 'https://speedy.test/v1/location/site/'
            && $request->data()['name'] === 'соф');
    }

    public function test_speedy_is_not_asked_for_sites_before_the_customer_types(): void
    {
        $this->fakeSpeedy();

        $this->assertSame([], $this->method()->places(''));

        Http::assertNothingSent();
    }

    public function test_the_offices_and_the_streets_of_a_site_are_listed(): void
    {
        $this->fakeSpeedy();

        $this->assertEquals(
            [new Point('77', 'СОФИЯ - ЦЕНТЪР'), new Point('78', 'СОФИЯ - МЛАДОСТ')],
            $this->method()->points('68134', DestinationType::Office),
        );
        Http::assertSent(fn (Request $request) => $request->url() === 'https://speedy.test/v1/location/office/'
            && $request->data()['siteId'] === 68134);

        $this->assertEquals([new Street('ВИТОША', '3109')], $this->method()->streets('68134', 'вит'));
        Http::assertSent(fn (Request $request) => $request->url() === 'https://speedy.test/v1/location/street/'
            && $request->data()['siteId'] === 68134
            && $request->data()['name'] === 'вит');

        // Speedy has no lockers behind the contract.
        $this->assertSame([], $this->method()->points('68134', DestinationType::Locker));
    }

    public function test_a_failing_speedy_gives_empty_lists(): void
    {
        Http::fake(['speedy.test/*' => Http::response(['error' => ['message' => 'Authentication failed']], 401)]);

        $this->assertSame([], $this->method()->places('соф'));
        $this->assertSame([], $this->method()->points('68134', DestinationType::Office));
        $this->assertSame([], $this->method()->streets('68134', ''));
    }

    public function test_the_sites_of_a_search_are_cached_only_once_speedy_answers(): void
    {
        Http::fakeSequence('speedy.test/*')
            ->push(['error' => ['message' => 'Authentication failed']], 401)
            ->push(['sites' => [['id' => 7, 'name' => 'Varna']]]);
        $service = new SpeedyService();

        $this->assertSame([], $service->getSites('var'));
        $this->assertSame('Varna', $service->getSites('var')[0]->name);
        $this->assertSame('Varna', $service->getSites('var')[0]->name);

        Http::assertSentCount(2);
    }
}
