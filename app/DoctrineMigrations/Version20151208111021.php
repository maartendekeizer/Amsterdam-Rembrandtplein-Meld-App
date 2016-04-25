<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151208111021 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE melding ADD laatste_bericht_reactie_id INT DEFAULT NULL');
        $this->addSql('UPDATE melding SET laatste_bericht_reactie_id = (SELECT MAX(reactie.id) FROM reactie WHERE reactie.melding_uuid = uuid AND reactie.type = \'Bericht\') WHERE laatste_bericht_reactie_id IS NULL');

        $this->addSql('ALTER TABLE melding ADD aantal_bericht_reacties INT NULL');
        $this->addSql('UPDATE melding SET aantal_bericht_reacties = (SELECT COUNT(reactie.id) FROM reactie WHERE reactie.melding_uuid = uuid AND reactie.type = \'Bericht\') WHERE aantal_bericht_reacties IS NULL');
        $this->addSql('ALTER TABLE melding ALTER COLUMN aantal_bericht_reacties SET NOT NULL');

        $this->addSql('ALTER TABLE melding ADD CONSTRAINT FK_A81CD4D7296F4707 FOREIGN KEY (laatste_bericht_reactie_id) REFERENCES reactie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A81CD4D7296F4707 ON melding (laatste_bericht_reactie_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE melding DROP CONSTRAINT FK_A81CD4D7296F4707');
        $this->addSql('DROP INDEX IDX_A81CD4D7296F4707');
        $this->addSql('ALTER TABLE melding DROP laatste_bericht_reactie_id');
        $this->addSql('ALTER TABLE melding DROP aantal_bericht_reacties');
    }
}
