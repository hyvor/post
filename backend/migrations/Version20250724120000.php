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
                user_id BIGINT PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                updated_at TIMESTAMPTZ NOT NULL
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sudo_users');
    }
}

