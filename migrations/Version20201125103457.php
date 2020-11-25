<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201125103457 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fund (id INT AUTO_INCREMENT NOT NULL, transaction_type_id INT NOT NULL, object VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, INDEX IDX_DC923E10B3E6B071 (transaction_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fund ADD CONSTRAINT FK_DC923E10B3E6B071 FOREIGN KEY (transaction_type_id) REFERENCES transaction_type (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fund DROP FOREIGN KEY FK_DC923E10B3E6B071');
        $this->addSql('DROP TABLE fund');
        $this->addSql('DROP TABLE transaction_type');
    }
}
