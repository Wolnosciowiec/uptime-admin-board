<?php declare(strict_types=1);

namespace Riotkit\UptimeAdminBoard\Provider;

use GuzzleHttp\Client;
use Riotkit\UptimeAdminBoard\Entity\Node;

class InfracheckProvider implements ServerUptimeProviderInterface
{
    private const PREFIX = 'Infracheck://';
    private const PROVIDER_NAME = 'Infracheck';

    /**
     * @inheritDoc
     */
    public function canHandle(string $url): bool
    {
        return str_starts_with($url, static::PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function handle(string $url): array
    {
        $url = substr($url, strlen(static::PREFIX));

        $http = new Client();
        $asArray = json_decode($http->get($url)->getBody()->getContents(), true);

        $result = [];

        foreach ($asArray['checks'] as $name => $details) {
            $result[] = new Node(
                name: $name,
                checkedBy: static::PROVIDER_NAME,
                status: $details['status'],
                url: $url,
                actionTime: $details['checked_at']
            );
        }

        return $result;
    }
}