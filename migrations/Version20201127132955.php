<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201127132955 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD deleted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE customer ADD deleted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE delivery ADD deleted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE delivery_man ADD deleted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE product ADD deleted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE provider ADD deleted VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD deleted TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP deleted');
        $this->addSql('ALTER TABLE customer DROP deleted');
        $this->addSql('ALTER TABLE delivery DROP deleted');
        $this->addSql('ALTER TABLE delivery_man DROP deleted');
        $this->addSql('ALTER TABLE product DROP deleted');
        $this->addSql('ALTER TABLE provider DROP deleted');
        $this->addSql('ALTER TABLE user DROP deleted');
    }
}
