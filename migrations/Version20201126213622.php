<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201126213622 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_return (id INT AUTO_INCREMENT NOT NULL, first_order_id INT NOT NULL, last_order_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_64112FDA39875F63 (first_order_id), UNIQUE INDEX UNIQ_64112FDAF5FE28A6 (last_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_return ADD CONSTRAINT FK_64112FDA39875F63 FOREIGN KEY (first_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_return ADD CONSTRAINT FK_64112FDAF5FE28A6 FOREIGN KEY (last_order_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_return');
    }
}
