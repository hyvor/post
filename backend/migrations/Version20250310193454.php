<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250310193454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sends table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TYPE send_status AS ENUM ('pending', 'sent', 'failed');
        SQL
        );

        $this->addSql(
            <<<SQL
        CREATE TABLE sends (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            newsletter_id BIGINT NOT NULL references newsletters(id) ON DELETE CASCADE,
            issue_id BIGINT NOT NULL references issues(id),
            subscriber_id BIGINT NOT NULL references subscribers(id) ON DELETE CASCADE,
            email TEXT NOT NULL,
            status send_status NOT NULL,
            error_private TEXT,
            failed_tries INT DEFAULT 0 NOT NULL,
            sent_at timestamptz,
            failed_at timestamptz,
            delivered_at timestamptz,
            unsubscribe_at timestamptz,
            bounced_at timestamptz,
            complained_at timestamptz,
            hard_bounce BOOLEAN DEFAULT FALSE NOT NULL
        );
        SQL
        );

        $this->addSql("CREATE INDEX idx_newsletter_id ON sends (newsletter_id)");
        $this->addSql("CREATE INDEX idx_issue_id ON sends (issue_id)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sends');
    }
}
