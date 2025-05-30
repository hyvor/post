<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250523132454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create exports table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE TYPE subscriber_export_status AS ENUM ('pending', 'completed', 'failed');"
        );

        $this->addSql(
            <<<SQL
        CREATE TABLE subscriber_exports (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            newsletter_id BIGINT NOT NULL references newsletters(id) ON DELETE CASCADE,
            media_id BIGINT references media(id) ON DELETE CASCADE,
            status subscriber_export_status NOT NULL DEFAULT 'pending',
            error_message TEXT
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscriber_exports');
    }
}