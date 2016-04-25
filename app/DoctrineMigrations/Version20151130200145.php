<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151130200145 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');


        $this->addSql('CREATE SEQUENCE cookie_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE melder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reactie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cookie_token (id INT NOT NULL, melder_id INT DEFAULT NULL, token VARCHAR(125) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6B086E3B5B93B57 ON cookie_token (melder_id)');
        $this->addSql('CREATE TABLE melder (id INT NOT NULL, naam VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, mobiel_nummer VARCHAR(11) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE melding (uuid VARCHAR(36) NOT NULL, melder_id INT DEFAULT NULL, secret VARCHAR(50) NOT NULL, aanmaak_datumtijd TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX IDX_A81CD4D75B93B57 ON melding (melder_id)');
        $this->addSql('CREATE TABLE reactie (id INT NOT NULL, melding_uuid VARCHAR(36) DEFAULT NULL, aanmaak_datumtijd TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, client_ip VARCHAR(128) DEFAULT NULL, afzender VARCHAR(255) DEFAULT NULL, bericht TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E97C03306D5BC1CF ON reactie (melding_uuid)');
        $this->addSql('ALTER TABLE cookie_token ADD CONSTRAINT FK_6B086E3B5B93B57 FOREIGN KEY (melder_id) REFERENCES melder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE melding ADD CONSTRAINT FK_A81CD4D75B93B57 FOREIGN KEY (melder_id) REFERENCES melder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reactie ADD CONSTRAINT FK_E97C03306D5BC1CF FOREIGN KEY (melding_uuid) REFERENCES melding (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cookie_token DROP CONSTRAINT FK_6B086E3B5B93B57');
        $this->addSql('ALTER TABLE melding DROP CONSTRAINT FK_A81CD4D75B93B57');
        $this->addSql('ALTER TABLE reactie DROP CONSTRAINT FK_E97C03306D5BC1CF');
        $this->addSql('DROP SEQUENCE cookie_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE melder_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reactie_id_seq CASCADE');
        $this->addSql('DROP TABLE cookie_token');
        $this->addSql('DROP TABLE melder');
        $this->addSql('DROP TABLE melding');
        $this->addSql('DROP TABLE reactie');

    }
}
