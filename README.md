# Laravel Shipping — Speedy

Speedy as a delivery method of a Laravel shop: the client of the Speedy API (on [`givanov95/typed-http`](https://github.com/givanov95/typed-http)) and the adapter that puts it behind the delivery contract of [`givanov95/laravel-shipping`](https://github.com/givanov95/laravel-shipping). The shop needs no code for Speedy: the package registers the method, the shop offers it.

```bash
composer require givanov95/laravel-shipping-speedy
```

> **0.x, not yet checked against the real API.** The requests and the answers are covered with `Http::fake()` only: the tests prove what is sent and how the answer we expect is read. Run the checks under [Before going live](#before-going-live) with your own account first.

## What the shop gets

The service provider is auto-discovered. It registers the method under the key `speedy` with the label `Speedy`:

| | |
| --- | --- |
| Delivers to | an office (`office`), or an address (`address`) in a site Speedy knows by id |
| Checkout lists | `PlaceDirectory`: the sites found by the text the customer types (cached a day per text), the offices of a site, its streets |
| Price | asked from Speedy per order (`calculate`), in the currency of its answer |
| Kept on the order | the `Destination`, with the ids of the site and of the street in `courierData` (`site_id`, `street_id`); a street Speedy does not list is kept and sent by its name |

Registered is not offered: which methods a customer can pick, and in what order, is the decision of the shop. It finds this one in the registry by its key, the key an order keeps: `app(Givanov95\Shipping\DeliveryMethods::class)->get('speedy')`.

Speedy prices in the currency of the contract. The delivery contract has no exchange rate, so a shop refuses the price for a cart in another currency.

## Configuration

```dotenv
SPEEDY_BASE_URL="https://api.speedy.bg/v1"
SPEEDY_USERNAME=
SPEEDY_PASSWORD=
SPEEDY_LANGUAGE=EN
SPEEDY_SENDER_CLIENT_ID=
SPEEDY_SENDER_OFFICE_ID=
SPEEDY_SENDER_NAME=
SPEEDY_SENDER_PHONE=
SPEEDY_PACKAGE_WEIGHT=1
SPEEDY_PACKAGE_DESCRIPTION="Parcel"
```

| Key | What it is |
| --- | --- |
| `SPEEDY_BASE_URL` | The API. Speedy has no separate sandbox: a demo account works on the live address |
| `SPEEDY_USERNAME`, `SPEEDY_PASSWORD`, `SPEEDY_LANGUAGE` | The account and the language of the names. Speedy has no auth header: the three travel in the body of every request |
| `SPEEDY_SENDER_*` | The client id of the contract, the office the parcels are dropped at, and the contact of the sender |
| `SPEEDY_PACKAGE_WEIGHT`, `SPEEDY_PACKAGE_DESCRIPTION` | The weight (kg) a delivery is priced for and the contents of the parcel |

The values are read through `config('speedy.*')`. To change the file itself:

```bash
php artisan vendor:publish --tag=speedy-config
```

## Before going live

Nothing here has been sent to Speedy yet. With a demo account, from `php artisan tinker`:

- A request without `userName`, `password` and `language` in the body is refused, and with them (`SpeedyConnector::defaultBody()`) it passes.
- `findSites` answers with `sites` (`id`, `name`, `postCode`), `getOffices` with `offices` (`id`, `name`), `findStreets` with `streets` (`id`, `name`).
- `calculate` answers with `calculations[0].price.total` and `calculations[0].price.currency`, for an office and for an address. A calculation without a currency is read as `BGN`: check that Speedy always names it, or open an issue.
- The service is fixed (`505`) and the sites are searched in Bulgaria (`countryId` 100): check both against your contract.

Only reading requests and `calculate`. `createShipment` creates a real shipment on the live API: do not send it without a demo account. Record what the API really answers as fixtures without personal data and tokens, and fix the code and the tests where they differ.

## Testing

```bash
composer test
```

## License

MIT
