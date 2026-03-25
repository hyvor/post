<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325123241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updates issues table to add a new column test_emails_sent';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                ALTER TABLE issues ADD COLUMN test_emails_sent INT NOT NULL DEFAULT 0;
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
