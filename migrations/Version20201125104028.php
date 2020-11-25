<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201125104028 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fund ADD manager_id INT NOT NULL');
        $this->addSql('ALTER TABLE fund ADD CONSTRAINT FK_DC923E10783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_DC923E10783E3463 ON fund (manager_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fund DROP FOREIGN KEY FK_DC923E10783E3463');
        $this->addSql('DROP INDEX IDX_DC923E10783E3463 ON fund');
        $this->addSql('ALTER TABLE fund DROP manager_id');
    }
}
