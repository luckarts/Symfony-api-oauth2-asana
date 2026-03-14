<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add milestones table (TM09-MVP)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TYPE milestone_status AS ENUM('pending', 'in_progress', 'completed')");
        $this->addSql('CREATE TABLE milestones (
            id UUID NOT NULL,
            project_id UUID NOT NULL,
            title VARCHAR(255) NOT NULL,
            due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            status milestone_status NOT NULL DEFAULT \'pending\',
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql("COMMENT ON COLUMN milestones.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN milestones.project_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN milestones.due_date IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN milestones.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN milestones.updated_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('ALTER TABLE milestones ADD CONSTRAINT fk_milestones_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_milestones_project ON milestones (project_id)');
        $this->addSql('CREATE INDEX idx_milestones_due_date ON milestones (due_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE milestones DROP CONSTRAINT fk_milestones_project');
        $this->addSql('DROP TABLE milestones');
        $this->addSql('DROP TYPE milestone_status');
    }
}
