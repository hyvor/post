<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create subscriber_list_removals table and subscribers email index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                CREATE TABLE subscriber_list_removals (
                    id            BIGSERIAL PRIMARY KEY,
                    list_id       BIGINT NOT NULL REFERENCES lists(id) ON DELETE CASCADE,
                    subscriber_id BIGINT NOT NULL REFERENCES subscribers(id) ON DELETE CASCADE,
                    reason        TEXT NOT NULL,
                    created_at    TIMESTAMPTZ NOT NULL,
                    UNIQUE(list_id, subscriber_id)
                )
                SQL,
        );
        $this->addSql('CREATE INDEX idx_subscribers_email ON subscribers (email)');
    }

    public function down(Schema $schema): void {}
}
