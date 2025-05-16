<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250516131323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sending_emails table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE sending_emails (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            email TEXT NOT NULL,
            project_id BIGINT NOT NULL references projects(id) ON DELETE CASCADE,
            custom_domain_id BIGINT NOT NULL references domains(id) ON DELETE CASCADE
        );
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sending_emails');
    }
}
