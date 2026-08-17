<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260719160654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add metadata column to newsletters table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                ALTER TABLE newsletters
                    ADD COLUMN metadata JSONB NOT NULL DEFAULT '{}',
                    ADD COLUMN created_by_source TEXT DEFAULT NULL,
                    ALTER COLUMN user_id DROP NOT NULL;
                SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                ALTER TABLE newsletters
                    DROP COLUMN metadata;
                SQL,
        );
    }
}
