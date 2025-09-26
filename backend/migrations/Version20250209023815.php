<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209023815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the newsletters table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        CREATE TABLE newsletters (
            id BIGSERIAL PRIMARY KEY,
            uuid UUID DEFAULT gen_random_uuid() NOT NULL,
            subdomain TEXT UNIQUE NOT NULL,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            user_id BIGINT NOT NULL,
            meta JSONB,
            name VARCHAR(255) NOT NULL,
            test_sent_emails JSONB
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE newsletters');
    }
}
