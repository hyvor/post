<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250520115428 extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Create media table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        CREATE TABLE media (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            project_id BIGINT NOT NULL references projects(id) ON DELETE CASCADE,
            type text NOT NULL,
            path text NOT NULL UNIQUE, -- ex: test.txt or import/import1.csv
            size bigint NOT NULL,
            extension text NOT NULL,
            UNIQUE (project_id, path)
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE media');
    }
}
