<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tasks table (Task POC)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tasks (
            id UUID NOT NULL,
            title VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT \'todo\',
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX idx_tasks_status ON tasks (status)');

        $this->addSql("COMMENT ON COLUMN tasks.id IS \'(DC2Type:uuid)\'");
        $this->addSql("COMMENT ON COLUMN tasks.created_at IS \'(DC2Type:datetime_immutable)\'");
        $this->addSql("COMMENT ON COLUMN tasks.updated_at IS \'(DC2Type:datetime_immutable)\'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tasks');
    }
}
