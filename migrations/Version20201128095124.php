<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201128095124 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_return DROP FOREIGN KEY FK_64112FDA39875F63');
        $this->addSql('ALTER TABLE order_return DROP FOREIGN KEY FK_64112FDAF5FE28A6');
        $this->addSql('ALTER TABLE order_return CHANGE last_order_id last_order_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_return ADD CONSTRAINT FK_64112FDA39875F63 FOREIGN KEY (first_order_id) REFERENCES payment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_return ADD CONSTRAINT FK_64112FDAF5FE28A6 FOREIGN KEY (last_order_id) REFERENCES payment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_return DROP FOREIGN KEY FK_64112FDA39875F63');
        $this->addSql('ALTER TABLE order_return DROP FOREIGN KEY FK_64112FDAF5FE28A6');
        $this->addSql('ALTER TABLE order_return CHANGE last_order_id last_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_return ADD CONSTRAINT FK_64112FDA39875F63 FOREIGN KEY (first_order_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE order_return ADD CONSTRAINT FK_64112FDAF5FE28A6 FOREIGN KEY (last_order_id) REFERENCES payment (id)');
    }
}
