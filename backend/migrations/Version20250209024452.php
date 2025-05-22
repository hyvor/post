<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209024452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create issues table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TYPE issues_status AS ENUM ('draft', 'scheduled', 'sending', 'failed', 'sent');
        SQL
        );

        $this->addSql(
            <<<SQL
        CREATE TABLE issues (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            uuid TEXT UNIQUE NOT NULL,
            project_id BIGINT NOT NULL references projects(id) ON DELETE CASCADE,
            subject VARCHAR(255),
            from_name VARCHAR(255),
            from_email TEXT NOT NULL,
            reply_to_email TEXT,
            content TEXT,
            status issues_status,
            list_ids jsonb,
            html TEXT,
            text TEXT,
            scheduled_at timestamptz,
            sending_at timestamptz,
            error_private TEXT,
            total_sends INT DEFAULT 0 NOT NULL,
            ok_sends INT DEFAULT 0 NOT NULL,
            failed_sends INT DEFAULT 0 NOT NULL,
            failed_at timestamptz,
            sent_at timestamptz
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE issues CASCADE');
        $this->addSql('DROP TYPE issues_status CASCADE');
    }
}
