<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260115120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add language_code and is_rtl columns to newsletters table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE newsletters
                ADD COLUMN language_code VARCHAR(10) DEFAULT NULL,
                ADD COLUMN is_rtl BOOLEAN NOT NULL DEFAULT FALSE;
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE newsletters
                DROP COLUMN language_code,
                DROP COLUMN is_rtl;
            SQL
        );
    }
}
