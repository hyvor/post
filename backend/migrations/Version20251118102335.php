<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118102335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Database structure updates';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE issues
                DROP COLUMN opened_sends,
                DROP COLUMN clicked_sends,

                DROP COLUMN failed_at,
                DROP COLUMN ok_sends,
                DROP COLUMN failed_sends,
                
                RENAME COLUMN total_sends TO total_sendable;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
