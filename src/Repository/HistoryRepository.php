<?php declare(strict_types=1);

namespace Riotkit\UptimeAdminBoard\Repository;

use Riotkit\UptimeAdminBoard\Collection\HistoriesCollection;
use Riotkit\UptimeAdminBoard\Collection\NodeHistoryCollection;
use Riotkit\UptimeAdminBoard\Entity\Node;

interface HistoryRepository
{
    public function persist(Node $node): void;

    public function removeOlderThanDays(int $maxDays): void;

    public function findSuccessCount(): int;

    public function findFailingCount(): int;

    public function findCountPerHour(): array;

    /**
     * @return HistoriesCollection
     */
    public function findAllGrouped(): HistoriesCollection;
}
