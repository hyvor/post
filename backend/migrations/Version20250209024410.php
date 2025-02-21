<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209024410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the subscribers table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TYPE subscriber_status AS ENUM ('subscribed', 'unsubscribed', 'pending');
        SQL);

        $this->addSql(<<<SQL
            CREATE TYPE subscriber_source AS ENUM ('console', 'form', 'import', 'auto_subscribe');
        SQL);

        $this->addSql(<<<SQL
        CREATE TABLE subscribers (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            list_id BIGINT NOT NULL references lists(id),
            email VARCHAR(255) NOT NULL UNIQUE,
            status subscriber_status DEFAULT 'pending',
            subscribed_at timestamptz,
            unsubscribed_at timestamptz,
            source subscriber_source DEFAULT 'form',
            source_id VARCHAR(255),
            subscribe_ip VARCHAR(255),
            unsubscribe_reason VARCHAR(255)
        );
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscribers');
        $this->addSql('DROP TYPE subscriber_status');
        $this->addSql('DROP TYPE subscriber_source');
    }
}
