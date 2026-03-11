<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create board_columns table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE board_columns (
            id UUID NOT NULL,
            project_id UUID NOT NULL,
            title VARCHAR(100) NOT NULL,
            position INTEGER NOT NULL DEFAULT 0,
            wip_limit INTEGER DEFAULT NULL,
            is_default BOOLEAN NOT NULL DEFAULT FALSE,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('ALTER TABLE board_columns ADD CONSTRAINT fk_board_columns_project
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE INDEX idx_board_columns_project_id ON board_columns (project_id)');
        $this->addSql('CREATE INDEX idx_board_columns_position ON board_columns (project_id, position)');
        $this->addSql('CREATE UNIQUE INDEX uq_board_columns_project_default ON board_columns (project_id) WHERE is_default = TRUE');

        $this->addSql("COMMENT ON COLUMN board_columns.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN board_columns.project_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN board_columns.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN board_columns.updated_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE board_columns');
    }
}
