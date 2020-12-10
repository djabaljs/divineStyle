<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201206224530 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE color_product');
        $this->addSql('ALTER TABLE provider_product ADD length_id INT DEFAULT NULL, ADD color_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE provider_product ADD CONSTRAINT FK_2312A60A61ED455A FOREIGN KEY (length_id) REFERENCES length (id)');
        $this->addSql('ALTER TABLE provider_product ADD CONSTRAINT FK_2312A60A7ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id)');
        $this->addSql('CREATE INDEX IDX_2312A60A61ED455A ON provider_product (length_id)');
        $this->addSql('CREATE INDEX IDX_2312A60A7ADA1FB5 ON provider_product (color_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE color_product (color_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_80DC89834584665A (product_id), INDEX IDX_80DC89837ADA1FB5 (color_id), PRIMARY KEY(color_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE color_product ADD CONSTRAINT FK_80DC89834584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE color_product ADD CONSTRAINT FK_80DC89837ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE provider_product DROP FOREIGN KEY FK_2312A60A61ED455A');
        $this->addSql('ALTER TABLE provider_product DROP FOREIGN KEY FK_2312A60A7ADA1FB5');
        $this->addSql('DROP INDEX IDX_2312A60A61ED455A ON provider_product');
        $this->addSql('DROP INDEX IDX_2312A60A7ADA1FB5 ON provider_product');
        $this->addSql('ALTER TABLE provider_product DROP length_id, DROP color_id');
    }
}
