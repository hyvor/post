<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209024328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the lists table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE lists (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP,
            project_id BIGINT NOT NULL references projects(id),
            name TEXT NOT NULL,
            description TEXT DEFAULT NULL
        );
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lists');
    }
}
