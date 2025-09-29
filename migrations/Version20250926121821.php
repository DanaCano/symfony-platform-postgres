<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250926121821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inicial: tablas users, project, application (PostgreSQL)';
    }

    public function up(Schema $schema): void
    {
        // users
        $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_users_email ON users (email)');

        // project
        $this->addSql('CREATE TABLE project (id SERIAL NOT NULL, owner_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_project_owner ON project (owner_id)');

        // application
        $this->addSql('CREATE TABLE application (id SERIAL NOT NULL, project_id INT NOT NULL, applicant_id INT NOT NULL, message TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_application_project ON application (project_id)');
        $this->addSql('CREATE INDEX IDX_application_applicant ON application (applicant_id)');

        // FKs
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_project_owner FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_application_project FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_application_applicant FOREIGN KEY (applicant_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_application_applicant');
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_application_project');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_project_owner');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE users');
    }
}
