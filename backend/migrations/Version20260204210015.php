<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260204210015 extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Create table for zenstruct/messenger-monitor-bundle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TABLE messenger_processed_messages (
                id SERIAL PRIMARY KEY,
                run_id INT NOT NULL,
                attempt INT NOT NULL DEFAULT 1,
                message_type VARCHAR(255) NOT NULL,
                description TEXT,
                dispatched_at TIMESTAMP WITH TIME ZONE NOT NULL,
                received_at TIMESTAMP WITH TIME ZONE NOT NULL,
                finished_at TIMESTAMP WITH TIME ZONE NOT NULL,
                memory_usage INT NOT NULL,
                transport VARCHAR(100) NOT NULL,
                tags TEXT,
                wait_time INT NOT NULL,
                handle_time INT NOT NULL,
                failure_type VARCHAR(255),
                failure_message TEXT,
                results JSONB
            );
            SQL
        );
    }

    public function down(Schema $schema): void
    {

    }
}
