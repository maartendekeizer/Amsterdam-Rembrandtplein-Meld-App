<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160127105516 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE melding ADD mora_gestart BOOLEAN NULL');
        $this->addSql('UPDATE melding SET mora_gestart = false WHERE mora_data_melder IS NULL');
        $this->addSql('UPDATE melding SET mora_gestart = true WHERE mora_gestart = false OR mora_gestart IS NULL');
        $this->addSql('ALTER TABLE melding ALTER COLUMN mora_gestart SET NOT NULL');

        $this->addSql('ALTER TABLE melding ADD melder_wil_mora BOOLEAN DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE melding DROP mora_gestart');
        $this->addSql('ALTER TABLE melding DROP melder_wil_mora');
    }
}
