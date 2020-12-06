<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202223109 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product_variation_color');
        $this->addSql('DROP TABLE product_variation_length');
        $this->addSql('DROP TABLE product_variation_product');
        $this->addSql('ALTER TABLE product_variation ADD color_id INT DEFAULT NULL, ADD length_id INT DEFAULT NULL, ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85677ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B856761ED455A FOREIGN KEY (length_id) REFERENCES length (id)');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85674584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_C3B85677ADA1FB5 ON product_variation (color_id)');
        $this->addSql('CREATE INDEX IDX_C3B856761ED455A ON product_variation (length_id)');
        $this->addSql('CREATE INDEX IDX_C3B85674584665A ON product_variation (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_variation_color (product_variation_id INT NOT NULL, color_id INT NOT NULL, INDEX IDX_AEA922B17ADA1FB5 (color_id), INDEX IDX_AEA922B1DC269DB3 (product_variation_id), PRIMARY KEY(product_variation_id, color_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE product_variation_length (product_variation_id INT NOT NULL, length_id INT NOT NULL, INDEX IDX_6405B81E61ED455A (length_id), INDEX IDX_6405B81EDC269DB3 (product_variation_id), PRIMARY KEY(product_variation_id, length_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE product_variation_product (product_variation_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_C4F93484584665A (product_id), INDEX IDX_C4F9348DC269DB3 (product_variation_id), PRIMARY KEY(product_variation_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_variation_color ADD CONSTRAINT FK_AEA922B17ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_variation_color ADD CONSTRAINT FK_AEA922B1DC269DB3 FOREIGN KEY (product_variation_id) REFERENCES product_variation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_variation_length ADD CONSTRAINT FK_6405B81E61ED455A FOREIGN KEY (length_id) REFERENCES length (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_variation_length ADD CONSTRAINT FK_6405B81EDC269DB3 FOREIGN KEY (product_variation_id) REFERENCES product_variation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_variation_product ADD CONSTRAINT FK_C4F93484584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_variation_product ADD CONSTRAINT FK_C4F9348DC269DB3 FOREIGN KEY (product_variation_id) REFERENCES product_variation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85677ADA1FB5');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B856761ED455A');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85674584665A');
        $this->addSql('DROP INDEX IDX_C3B85677ADA1FB5 ON product_variation');
        $this->addSql('DROP INDEX IDX_C3B856761ED455A ON product_variation');
        $this->addSql('DROP INDEX IDX_C3B85674584665A ON product_variation');
        $this->addSql('ALTER TABLE product_variation DROP color_id, DROP length_id, DROP product_id');
    }
}
