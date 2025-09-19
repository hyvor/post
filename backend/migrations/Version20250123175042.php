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
        // Enum: RelayDomainStatus -> 'pending', 'active', 'warning', suspended'
        $this->addSql(<<<SQL
        CREATE TYPE relay_domain_status AS ENUM ('pending', 'active', 'warning', 'suspended');
        SQL
        );

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
            verified_in_relay BOOLEAN DEFAULT FALSE,
            relay_status relay_domain_status DEFAULT 'pending',
            relay_last_checked_at timestamptz DEFAULT NULL,
            relay_error_message TEXT DEFAULT NULL
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
