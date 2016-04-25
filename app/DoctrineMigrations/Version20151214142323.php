<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151214142323 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE handhaver ADD telefoon VARCHAR(11) DEFAULT NULL');

        $this->addSql('ALTER TABLE melding ADD handhaver_id INT NULL');
        $this->addSql('UPDATE melding SET handhaver_id = (SELECT id FROM handhaver LIMIT 1)');
        $this->addSql('ALTER TABLE melding ALTER COLUMN handhaver_id SET NOT NULL');
        $this->addSql('ALTER TABLE melding ADD CONSTRAINT FK_A81CD4D7F020EF81 FOREIGN KEY (handhaver_id) REFERENCES handhaver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A81CD4D7F020EF81 ON melding (handhaver_id)');

        $this->addSql('ALTER TABLE reactie ADD handhaver_id INT DEFAULT NULL');
        $this->addSql('UPDATE reactie SET handhaver_id = (SELECT id FROM handhaver LIMIT 1) WHERE afzender = \'Handhaver\'');
        $this->addSql('ALTER TABLE reactie ADD CONSTRAINT FK_E97C0330F020EF81 FOREIGN KEY (handhaver_id) REFERENCES handhaver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E97C0330F020EF81 ON reactie (handhaver_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE melding DROP CONSTRAINT FK_A81CD4D7F020EF81');
        $this->addSql('DROP INDEX IDX_A81CD4D7F020EF81');
        $this->addSql('ALTER TABLE melding DROP handhaver_id');
        $this->addSql('ALTER TABLE reactie DROP CONSTRAINT FK_E97C0330F020EF81');
        $this->addSql('DROP INDEX IDX_E97C0330F020EF81');
        $this->addSql('ALTER TABLE reactie DROP handhaver_id');
        $this->addSql('ALTER TABLE handhaver DROP telefoon');
    }
}
