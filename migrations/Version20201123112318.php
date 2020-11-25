<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201123112318 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091F5E0588');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091F5E0588 FOREIGN KEY (shops_id) REFERENCES shop (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091F5E0588');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091F5E0588 FOREIGN KEY (shops_id) REFERENCES shop (id)');
    }
}
