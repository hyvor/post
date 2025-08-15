<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123175042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create domains table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE domains (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            domain TEXT NOT NULL UNIQUE,
            dkim_host TEXT NOT NULL,
            dkim_txt_value TEXT NOT NULL,
            user_id BIGINT NOT NULL,
            relay_id BIGINT NOT NULL,
            verified_in_relay BOOLEAN DEFAULT FALSE
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE domains');
    }
}
