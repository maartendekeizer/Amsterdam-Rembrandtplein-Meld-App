<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151216133424 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE melding ADD gewijzigd_datumtijd TIMESTAMP(0) WITHOUT TIME ZONE NULL');
        $this->addSql('UPDATE melding SET gewijzigd_datumtijd = (SELECT MAX(aanmaak_datumtijd) FROM reactie WHERE reactie.melding_uuid = uuid)');
        $this->addSql('UPDATE melding SET gewijzigd_datumtijd = aanmaak_datumtijd WHERE gewijzigd_datumtijd IS NULL');
        $this->addSql('ALTER TABLE melding ALTER COLUMN gewijzigd_datumtijd SET NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE melding DROP gewijzigd_datumtijd');
    }
}
