<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160111154033 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE reactie ADD beoordeling INT DEFAULT NULL');
        $this->addSql('UPDATE reactie SET beoordeling = -2 WHERE SUBSTRING(bericht from 1 for 3) = \':-@\'');
        $this->addSql('UPDATE reactie SET beoordeling = -1 WHERE SUBSTRING(bericht from 1 for 3) = \':-(\'');
        $this->addSql('UPDATE reactie SET beoordeling = 0 WHERE SUBSTRING(bericht from 1 for 3) = \':-|\'');
        $this->addSql('UPDATE reactie SET beoordeling = 1 WHERE SUBSTRING(bericht from 1 for 3) = \':-)\'');
        $this->addSql('UPDATE reactie SET beoordeling = 2 WHERE SUBSTRING(bericht from 1 for 3) = \':-D\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE reactie DROP beoordeling');
    }
}
