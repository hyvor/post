<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518004215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create imports table';
    }

    public function up(Schema $schema): void
    {
        // TODO: remove this
        $this->addSql(
            <<<SQL
        CREATE TABLE imports (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            project_id BIGINT NOT NULL references projects(id),
            filename VARCHAR(255) NOT NULL
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
