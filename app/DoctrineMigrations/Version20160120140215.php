<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160120140215 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE melding ADD feedback_van_melder_toestaan BOOLEAN NULL');
        $this->addSql('UPDATE melding SET feedback_van_melder_toestaan = false');
        $this->addSql('ALTER TABLE melding ALTER COLUMN feedback_van_melder_toestaan SET NOT NULL');

        $this->addSql('ALTER TABLE melding ADD mora_starten_toestaan BOOLEAN NULL');
        $this->addSql('UPDATE melding SET mora_starten_toestaan = false');
        $this->addSql('ALTER TABLE melding ALTER COLUMN mora_starten_toestaan SET NOT NULL');

        $this->addSql('ALTER TABLE melding ADD mora_verstuurd BOOLEAN NULL');
        $this->addSql('UPDATE melding SET mora_verstuurd = false');
        $this->addSql('ALTER TABLE melding ALTER COLUMN mora_verstuurd SET NOT NULL');

        $this->addSql('ALTER TABLE melding ADD mora_data_melder JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE melding ADD mora_data_handhaver JSON DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE melding DROP feedback_van_melder_toestaan');
        $this->addSql('ALTER TABLE melding DROP mora_starten_toestaan');
        $this->addSql('ALTER TABLE melding DROP mora_data_melder');
        $this->addSql('ALTER TABLE melding DROP mora_data_handhaver');
        $this->addSql('ALTER TABLE melding DROP mora_verstuurd');
    }
}
