<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1536233320MessageQueueStats extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233320;
    }

    public function update(Connection $connection): void
    {
        $connection->exec('
          CREATE TABLE message_queue_stats (
            `id` BINARY(16) NOT NULL PRIMARY KEY,          
            `name` VARCHAR(255) NOT NULL,
            `size` INT(11) NOT NULL DEFAULT 0,
            CONSTRAINT `uniq.message_queue_stats.name` UNIQUE(`name`),
            CONSTRAINT `check.message_queue_stats.size` CHECK (size >= 0)
        )');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
