<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260424091842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add role column to sudo_users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE sudo_users ADD COLUMN role TEXT NOT NULL DEFAULT 'sudo';
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sudo_users DROP COLUMN role');
    }
}

