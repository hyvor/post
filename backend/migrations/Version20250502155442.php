<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250502155442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_invites table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        CREATE TABLE user_invites (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            newsletter_id BIGINT NOT NULL references newsletters(id),
            hyvor_user_id BIGINT NOT NULL,
            code VARCHAR(255) NOT NULL UNIQUE,
            expires_at timestamptz NOT NULL,
            role user_role NOT NULL,
            UNIQUE (newsletter_id, hyvor_user_id)
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_invites');
    }
}
