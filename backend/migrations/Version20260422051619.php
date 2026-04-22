<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422051619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add subscribers email index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_subscribers_email ON subscribers (email)');
    }

    public function down(Schema $schema): void {}
}
