<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224133540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Pivot table for list and subscriber';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE list_subscriber (
                list_id BIGINT NOT NULL REFERENCES lists(id) ON DELETE CASCADE,
                subscriber_id BIGINT NOT NULL REFERENCES subscribers(id) ON DELETE CASCADE
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE list_subscriber');
    }
}
