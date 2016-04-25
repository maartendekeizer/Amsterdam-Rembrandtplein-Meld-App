<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151214132931 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE dienst_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dienst (id INT NOT NULL, handhaver_id INT DEFAULT NULL, start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, eind TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AB4E87D8F020EF81 ON dienst (handhaver_id)');
        $this->addSql('ALTER TABLE dienst ADD CONSTRAINT FK_AB4E87D8F020EF81 FOREIGN KEY (handhaver_id) REFERENCES handhaver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('INSERT INTO dienst (id, start, eind, handhaver_id) VALUES (NEXTVAL(\'dienst_id_seq\'), \'2015-12-14 13:33:12\', NULL, (SELECT id FROM handhaver LIMIT 1))');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE dienst_id_seq CASCADE');
        $this->addSql('DROP TABLE dienst');
    }
}
