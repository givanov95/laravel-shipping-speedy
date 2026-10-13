<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy;

use Givanov95\TypedHttp\Connector;

/**
 * The Speedy API: base URL and credentials from config/speedy.php. The API has no auth header,
 * it expects userName, password and language inside the JSON body of every request.
 */
final class SpeedyConnector extends Connector
{
    public function baseUrl(): string
    {
        return (string) config('speedy.base_url');
    }

    /**
     * @return array<string,mixed>
     */
    public function defaultBody(): array
    {
        return [
            'userName' => (string) config('speedy.username'),
            'password' => (string) config('speedy.password'),
            'language' => (string) config('speedy.language'),
        ];
    }
}
