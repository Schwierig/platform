<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1552483292PromotionSalesChannel extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1552483292;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
        CREATE TABLE IF NOT EXISTS `promotion_sales_channel` (
          `promotion_id` BINARY(16) NOT NULL,
          `sales_channel_id` BINARY(16) NOT NULL,
          `priority` INT NOT NULL,
          PRIMARY KEY (`promotion_id`, `sales_channel_id`),
          INDEX `idx.promotion_sales_channel.sales_channel_id` (`sales_channel_id` ASC),
          INDEX `idx.promotion_sales_channel.promotion_id` (`promotion_id` ASC),
          CONSTRAINT `fk.promotion_sales_channel.promotion_id`
            FOREIGN KEY (`promotion_id`)
            REFERENCES `promotion` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
          CONSTRAINT `fk.promotion_sales_channel.sales_channel_id`
            FOREIGN KEY (`sales_channel_id`)
            REFERENCES `sales_channel` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION)
        ENGINE = InnoDB
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
