<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202230846 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE color_product (color_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_80DC89837ADA1FB5 (color_id), INDEX IDX_80DC89834584665A (product_id), PRIMARY KEY(color_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE height (id INT AUTO_INCREMENT NOT NULL, register_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_F54DE50F4976CB7E (register_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE length_product (length_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_6FA0EC461ED455A (length_id), INDEX IDX_6FA0EC44584665A (product_id), PRIMARY KEY(length_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE width (id INT AUTO_INCREMENT NOT NULL, register_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_8C1A452F4976CB7E (register_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE color_product ADD CONSTRAINT FK_80DC89837ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE color_product ADD CONSTRAINT FK_80DC89834584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE height ADD CONSTRAINT FK_F54DE50F4976CB7E FOREIGN KEY (register_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE length_product ADD CONSTRAINT FK_6FA0EC461ED455A FOREIGN KEY (length_id) REFERENCES length (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE length_product ADD CONSTRAINT FK_6FA0EC44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE width ADD CONSTRAINT FK_8C1A452F4976CB7E FOREIGN KEY (register_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_variation ADD shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85674D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_C3B85674D16C4DD ON product_variation (shop_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE color_product');
        $this->addSql('DROP TABLE height');
        $this->addSql('DROP TABLE length_product');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE width');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85674D16C4DD');
        $this->addSql('DROP INDEX IDX_C3B85674D16C4DD ON product_variation');
        $this->addSql('ALTER TABLE product_variation DROP shop_id');
    }
}
