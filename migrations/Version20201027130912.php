<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201027130912 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E094D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_81398E094D16C4DD ON customer (shop_id)');
        $this->addSql('ALTER TABLE shop DROP FOREIGN KEY FK_AC6A4CA2C3568B40');
        $this->addSql('DROP INDEX IDX_AC6A4CA2C3568B40 ON shop');
        $this->addSql('ALTER TABLE shop DROP customers_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E094D16C4DD');
        $this->addSql('DROP INDEX IDX_81398E094D16C4DD ON customer');
        $this->addSql('ALTER TABLE customer DROP shop_id');
        $this->addSql('ALTER TABLE shop ADD customers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA2C3568B40 FOREIGN KEY (customers_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_AC6A4CA2C3568B40 ON shop (customers_id)');
    }
}
