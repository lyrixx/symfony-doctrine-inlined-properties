<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220404134732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add initial migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE block (id UUID NOT NULL, page_id UUID NOT NULL, name VARCHAR(255) NOT NULL, configuration JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_831B9722C4663E4 ON block (page_id)');
        $this->addSql('CREATE TABLE page (id UUID NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE block DROP CONSTRAINT FK_831B9722C4663E4');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE page');
    }
}
