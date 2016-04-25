<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160203110722 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE melding DROP mora_starten_toestaan');
        $this->addSql('ALTER TABLE melding DROP mora_verstuurd');
        $this->addSql('ALTER TABLE melding DROP mora_data_melder');
        $this->addSql('ALTER TABLE melding DROP mora_data_handhaver');
        $this->addSql('ALTER TABLE melding DROP melder_wil_mora');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE melding ADD mora_starten_toestaan BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE melding ADD mora_verstuurd BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE melding ADD mora_data_melder JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE melding ADD mora_data_handhaver JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE melding ADD melder_wil_mora BOOLEAN DEFAULT NULL');
    }
}
