<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209024451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sending_profiles table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        CREATE TABLE sending_profiles (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            newsletter_id BIGINT NOT NULL references newsletters(id) ON DELETE CASCADE,
            domain_id BIGINT references domains(id) ON DELETE CASCADE,
            is_system BOOLEAN DEFAULT FALSE NOT NULL,
            is_default BOOLEAN DEFAULT FALSE NOT NULL,
            from_email TEXT DEFAULT NULL,
            from_name TEXT DEFAULT NULL,
            reply_to_email TEXT DEFAULT NULL,
            brand_name TEXT DEFAULT NULL,
            brand_logo TEXT DEFAULT NULL,
            UNIQUE (newsletter_id, from_email)
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sending_profiles');
    }
}
