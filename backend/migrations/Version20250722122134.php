<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250722122134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create approvals table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE TYPE approval_status AS ENUM ('pending', 'reviewing', 'approved', 'rejected');"
        );

        $this->addSql(<<<SQL
            CREATE TABLE approvals (
                id BIGSERIAL PRIMARY KEY,
                created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                user_id BIGINT NOT NULL UNIQUE,
                status approval_status NOT NULL DEFAULT 'pending',
                company_name VARCHAR(255) NOT NULL,
                country VARCHAR(255) NOT NULL,
                website TEXT NOT NULL,
                social_links TEXT,
                other_info JSONB,
                public_note VARCHAR(255),
                private_note VARCHAR(255)
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE approvals');
    }
}
