<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209023815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the projects table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE projects (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            user_id BIGINT NOT NULL,
            meta JSONB,
            name VARCHAR(255) NOT NULL
        );
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE projects');
    }
}
