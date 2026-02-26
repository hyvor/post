<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create list_subscriber_unsubscribed table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE list_subscriber_unsubscribed (
                id            BIGSERIAL PRIMARY KEY,
                list_id       BIGINT NOT NULL REFERENCES lists(id) ON DELETE CASCADE,
                subscriber_id BIGINT NOT NULL REFERENCES subscribers(id) ON DELETE CASCADE,
                created_at    TIMESTAMPTZ NOT NULL,
                UNIQUE(list_id, subscriber_id)
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE list_subscriber_unsubscribed');
    }
}
