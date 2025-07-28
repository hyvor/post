<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250724120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sudo_users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE sudo_users (
                id BIGSERIAL PRIMARY KEY,
                created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                user_id BIGINT NOT NULL UNIQUE
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sudo_users');
    }
}

