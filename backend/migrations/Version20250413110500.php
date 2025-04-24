<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413110500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cache_items table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE cache_items (
            item_id VARCHAR(255) PRIMARY KEY,
            item_data BYTEA,
            item_lifetime INTEGER,
            item_time INTEGER NOT NULL
        );
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cache_items');
    }
}
