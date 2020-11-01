<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201028120214 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E094D16C4DD');
        $this->addSql('DROP INDEX IDX_81398E094D16C4DD ON customer');
        $this->addSql('ALTER TABLE customer CHANGE shop_id shops_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091F5E0588 FOREIGN KEY (shops_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_81398E091F5E0588 ON customer (shops_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091F5E0588');
        $this->addSql('DROP INDEX IDX_81398E091F5E0588 ON customer');
        $this->addSql('ALTER TABLE customer CHANGE shops_id shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E094D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_81398E094D16C4DD ON customer (shop_id)');
    }
}
