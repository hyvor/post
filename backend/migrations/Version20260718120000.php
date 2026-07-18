<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260718120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add daily_sending_rate to newsletters and queued_at to issues';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE newsletters ADD COLUMN daily_sending_rate INT DEFAULT NULL;
        SQL
        );
        $this->addSql(<<<SQL
            ALTER TABLE issues ADD COLUMN queued_at timestamptz DEFAULT NULL;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE newsletters DROP COLUMN daily_sending_rate');
        $this->addSql('ALTER TABLE issues DROP COLUMN queued_at');
    }
}
