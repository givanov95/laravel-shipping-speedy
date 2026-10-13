<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Tests;

use Givanov95\Shipping\Speedy\Requests\FindSiteRequest;
use Givanov95\Shipping\Speedy\Requests\FindStreetRequest;
use Givanov95\Shipping\Speedy\Requests\GetOfficesRequest;
use Givanov95\Shipping\Speedy\Shipments\SpeedyOfficeShipment;
use Givanov95\Shipping\Speedy\Speedy;
use Givanov95\TypedHttp\Exceptions\RequestException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

final class SpeedyClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'speedy.username' => 'speedy-user',
            'speedy.password' => 'speedy-pass',
            'speedy.language' => 'BG',
        ]);
    }

    // typed-http 1.x dropped the credentials when they were declared in BaseSpeedyRequest.
    public function test_every_request_carries_the_credentials_in_the_body(): void
    {
        Http::fake(['speedy.test/*' => Http::response(['sites' => [], 'offices' => [], 'streets' => []])]);
        $speedy = new Speedy();

        $speedy->findSites(new FindSiteRequest(name: 'Varna'));
        $speedy->getOffices(new GetOfficesRequest(siteId: 7));
        $speedy->findStreets(new FindStreetRequest(siteId: 7, name: 'Main'));

        $credentials = ['userName' => 'speedy-user', 'password' => 'speedy-pass', 'language' => 'BG'];
        Http::assertSentCount(3);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://speedy.test/v1/location/site/'
            && $request->method() === 'POST'
            && $request->header('Content-Type') === ['application/json']
            && $request->data() === [...$credentials, 'countryId' => '100', 'name' => 'Varna']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://speedy.test/v1/location/office/'
            && $request->data() === [...$credentials, 'countryId' => '100', 'siteId' => 7]);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://speedy.test/v1/location/street/'
            && $request->data() === [...$credentials, 'siteId' => 7, 'name' => 'Main']);
    }

    public function test_a_shipment_reads_the_price_from_the_calculation(): void
    {
        Http::fake(['speedy.test/*' => Http::response(['calculations' => [['price' => ['total' => 6.2, 'currency' => 'BGN']]]])]);

        $shipment = new SpeedyOfficeShipment(
            senderClientId: 1,
            senderOfficeId: 2,
            senderName: 'Shop',
            senderPhone: '+359888000000',
            receiverOfficeId: 3,
            receiverName: 'John Doe',
            receiverPhone: '+359888888888',
            packageWeight: 1.0,
            description: 'Parcel',
        );

        $this->assertSame('6.20', (string) $shipment->getPrice()->getAmount());
        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return $request->url() === 'https://speedy.test/v1/calculate/'
                && $data['userName'] === 'speedy-user'
                && $data['service']['serviceIds'] === [505]
                && $data['recipient']['pickupOfficeId'] === 3;
        });
    }

    public function test_an_error_response_raises_a_request_exception(): void
    {
        Http::fake(['speedy.test/*' => Http::response(['error' => ['message' => 'Authentication failed']], 401)]);

        try {
            (new Speedy())->findSites(new FindSiteRequest(name: 'Varna'));
            $this->fail('RequestException expected');
        } catch (RequestException $e) {
            $this->assertSame(401, $e->status());
            $this->assertSame('Authentication failed', $e->response()->json('error.message'));
        }
    }
}
