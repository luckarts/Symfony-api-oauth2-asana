<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add milestone_id FK on tasks (nullable, SET NULL)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks ADD milestone_id UUID DEFAULT NULL');
        $this->addSql("COMMENT ON COLUMN tasks.milestone_id IS '(DC2Type:uuid)'");
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT fk_tasks_milestone FOREIGN KEY (milestone_id) REFERENCES milestones (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_tasks_milestone ON tasks (milestone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT fk_tasks_milestone');
        $this->addSql('DROP INDEX idx_tasks_milestone');
        $this->addSql('ALTER TABLE tasks DROP COLUMN milestone_id');
    }
}
