<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add parent_task_id self-referencing FK to tasks (TM03-F002)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks ADD COLUMN parent_task_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT fk_tasks_parent FOREIGN KEY (parent_task_id) REFERENCES tasks (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_tasks_parent ON tasks (parent_task_id)');
        $this->addSql("COMMENT ON COLUMN tasks.parent_task_id IS '(DC2Type:uuid)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT fk_tasks_parent');
        $this->addSql('DROP INDEX idx_tasks_parent');
        $this->addSql('ALTER TABLE tasks DROP COLUMN parent_task_id');
    }
}
