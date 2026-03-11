<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id to projects table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE projects ADD COLUMN user_id VARCHAR(36) NOT NULL DEFAULT ''");
        $this->addSql('ALTER TABLE projects ALTER COLUMN user_id DROP DEFAULT');
        $this->addSql('CREATE INDEX idx_projects_user_id ON projects (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_projects_user_id');
        $this->addSql('ALTER TABLE projects DROP COLUMN user_id');
    }
}
