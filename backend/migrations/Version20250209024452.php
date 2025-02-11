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
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TYPE issues_status AS ENUM ('draft', 'scheduled', 'sending', 'failed', 'sent');
        CREATE TABLE issues (
            id BIGSERIAL PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            uuid VARCHAR(255) UNIQUE NOT NULL,
            list_id BIGINT NOT NULL references lists(id),
            subject VARCHAR(255),
            from_name VARCHAR(255),
            from_email VARCHAR(255) NOT NULL,
            reply_to_email VARCHAR(255),
            content TEXT NULL,
            status issues_status,
            html TEXT NULL,
            text TEXT NULL,
            scheduled_at TIMESTAMP,
            sending_at TIMESTAMP,
            failed_at TIMESTAMP,
            sent_at TIMESTAMP,
            error_private TEXT,
            batch_id VARCHAR(255) UNIQUE
        );
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE issues');
        $this->addSql('DROP TYPE issues_status');
    }
}
