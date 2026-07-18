<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260718102910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop user_invites table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_invites');
    }

    public function down(Schema $schema): void {}
}
