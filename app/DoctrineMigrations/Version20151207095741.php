<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151207095741 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE UNIQUE INDEX uq__cookie_token__token ON cookie_token (token)');
        $this->addSql('CREATE INDEX ix__melding__aanmaak_datumtijd ON melding (aanmaak_datumtijd)');
        $this->addSql('CREATE INDEX ix__melding__melder_id__aanmaak_datumtijd ON melding (melder_id, aanmaak_datumtijd)');
        $this->addSql('CREATE INDEX ix__melding__is_verstuurd ON melding (is_verstuurd)');
        $this->addSql('CREATE INDEX ix__melding__is_gelezen ON melding (is_verstuurd)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX uq__cookie_token__token');
        $this->addSql('DROP INDEX ix__melding__aanmaak_datumtijd');
        $this->addSql('DROP INDEX ix__melding__melder_id__aanmaak_datumtijd');
        $this->addSql('DROP INDEX ix__melding__is_verstuurd');
        $this->addSql('DROP INDEX ix__melding__is_gelezen');
    }
}
