<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519075019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'subscriber_metadata_definitions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        CREATE TABLE subscriber_metadata_definitions (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            newsletter_id BIGINT NOT NULL references newsletters(id) ON DELETE CASCADE,
            key VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(255) NOT NULL,
            UNIQUE (newsletter_id, key)
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscriber_metadata_definitions');
    }
}
