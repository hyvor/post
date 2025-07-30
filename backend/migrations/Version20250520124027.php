<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250520124027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create imports table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE TYPE subscriber_import_status AS ENUM ('requires_input', 'importing', 'failed', 'completed');"
        );

        $this->addSql(
            <<<SQL
        CREATE TABLE subscriber_imports (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            newsletter_id BIGINT NOT NULL references newsletters(id) ON DELETE CASCADE,
            media_id BIGINT NOT NULL references media(id) ON DELETE CASCADE,
            status subscriber_import_status NOT NULL DEFAULT 'requires_input',
            fields JSONB,
            csv_fields JSONB,
            imported_subscribers INTEGER,
            warnings TEXT,
            error_message TEXT
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscriber_imports');
    }
}
