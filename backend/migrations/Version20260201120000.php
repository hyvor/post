<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds schema changes for organization migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE domains ADD COLUMN organization_id BIGINT DEFAULT NULL;
            SQL
        );
        $this->addSql(
            <<<SQL
            ALTER TABLE newsletters 
                ADD COLUMN organization_id BIGINT DEFAULT NULL,
                ADD COLUMN created_by_user_id BIGINT DEFAULT NULL;
            SQL
        );
        $this->addSql(
            <<<SQL
            ALTER TABLE approvals ADD COLUMN organization_id BIGINT DEFAULT NULL;
            SQL
        );
        $this->addSql(
            <<<SQL
            ALTER TABLE users ADD COLUMN organization_id BIGINT DEFAULT NULL;
            SQL
        );
        $this->addSql(
            <<<SQL
            ALTER TABLE users
                DROP CONSTRAINT users_newsletter_id_hyvor_user_id_key,
                ADD CONSTRAINT users_newsletter_id_hyvor_user_id_organization_id_key
                    UNIQUE (newsletter_id, hyvor_user_id, organization_id);
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
