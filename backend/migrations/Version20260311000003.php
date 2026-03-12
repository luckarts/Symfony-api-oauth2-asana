<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enrich tasks — project_id FK, column_id FK, due_date, is_completed, order_index (TM03-S003)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks ADD COLUMN project_id UUID NOT NULL');
        $this->addSql('ALTER TABLE tasks ADD COLUMN column_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE tasks ADD COLUMN due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE tasks ADD COLUMN is_completed BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE tasks ADD COLUMN order_index INTEGER NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT fk_tasks_column FOREIGN KEY (column_id) REFERENCES board_columns (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_tasks_project ON tasks (project_id)');
        $this->addSql("COMMENT ON COLUMN tasks.project_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN tasks.column_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN tasks.due_date IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT fk_tasks_column');
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT fk_tasks_project');
        $this->addSql('DROP INDEX idx_tasks_project');
        $this->addSql('ALTER TABLE tasks DROP COLUMN project_id');
        $this->addSql('ALTER TABLE tasks DROP COLUMN column_id');
        $this->addSql('ALTER TABLE tasks DROP COLUMN due_date');
        $this->addSql('ALTER TABLE tasks DROP COLUMN is_completed');
        $this->addSql('ALTER TABLE tasks DROP COLUMN order_index');
    }
}
