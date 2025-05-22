<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250502144042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TYPE user_role AS ENUM ('owner', 'admin');
        SQL
        );

        $this->addSql(
            <<<SQL
        CREATE TABLE users (
            id BIGSERIAL PRIMARY KEY,
            created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
            newsletter_id BIGINT NOT NULL references newsletters(id) ON DELETE CASCADE,
            hyvor_user_id BIGINT NOT NULL,
            role user_role NOT NULL,
            UNIQUE (newsletter_id, hyvor_user_id)
        );
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
    }
}
