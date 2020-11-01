<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201026201302 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop DROP FOREIGN KEY FK_AC6A4CA29395C3F3');
        $this->addSql('DROP INDEX IDX_AC6A4CA29395C3F3 ON shop');
        $this->addSql('ALTER TABLE shop ADD customers_id INT DEFAULT NULL, CHANGE customer_id staffs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA22A94E7F FOREIGN KEY (staffs_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA2C3568B40 FOREIGN KEY (customers_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_AC6A4CA22A94E7F ON shop (staffs_id)');
        $this->addSql('CREATE INDEX IDX_AC6A4CA2C3568B40 ON shop (customers_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494D16C4DD');
        $this->addSql('DROP INDEX IDX_8D93D6494D16C4DD ON user');
        $this->addSql('ALTER TABLE user DROP shop_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop DROP FOREIGN KEY FK_AC6A4CA22A94E7F');
        $this->addSql('ALTER TABLE shop DROP FOREIGN KEY FK_AC6A4CA2C3568B40');
        $this->addSql('DROP INDEX IDX_AC6A4CA22A94E7F ON shop');
        $this->addSql('DROP INDEX IDX_AC6A4CA2C3568B40 ON shop');
        $this->addSql('ALTER TABLE shop ADD customer_id INT DEFAULT NULL, DROP staffs_id, DROP customers_id');
        $this->addSql('ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA29395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_AC6A4CA29395C3F3 ON shop (customer_id)');
        $this->addSql('ALTER TABLE user ADD shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6494D16C4DD ON user (shop_id)');
    }
}
