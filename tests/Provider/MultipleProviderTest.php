<?php declare(strict_types=1);

namespace Tests\Riotkit\UptimeAdminBoard\Provider;

use Tests\TestCase;
use Riotkit\UptimeAdminBoard\Provider\DummyProvider;
use Riotkit\UptimeAdminBoard\Provider\MultipleProvider;
use Riotkit\UptimeAdminBoard\Entity\Node;

/**
 * @see MultipleProvider
 */
class MultipleProviderTest extends TestCase
{
    /**
     * @see MultipleProvider::handle()
     */
    public function test_handles_from_at_least_one_provider(): void
    {
        $multipleProvider = new MultipleProvider([
            new DummyProvider([new Node('iwa-ait.org', 'Dummy', Node::STATUS_UP, 'http://iwa-ait.org')], false),
            new DummyProvider([new Node('iwa-ait.org', 'Dummy', Node::STATUS_UNKNOWN)], true)
        ]);

        $this->assertCount(1, $multipleProvider->handle('DummyProvider://some-api-key'));
    }

    /**
     * @see MultipleProvider::handle()
     */
    public function test_returns_nothing_if_no_handler_reported_to_be_able_to_handle(): void
    {
        $multipleProvider = new MultipleProvider([
            new DummyProvider([new Node('iwa-ait.org', 'Dummy', Node::STATUS_UP, 'http://iwa-ait.org')], false),
            new DummyProvider([new Node('iwa-ait.org', 'Dummy', Node::STATUS_UNKNOWN)], false)
        ]);

        $this->assertCount(0, $multipleProvider->handle('DummyProvider://some-api-key'));
    }
}
