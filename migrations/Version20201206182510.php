<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201206182510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE color_product');
        $this->addSql('ALTER TABLE color ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE length ADD slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE color_product (color_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_80DC89837ADA1FB5 (color_id), INDEX IDX_80DC89834584665A (product_id), PRIMARY KEY(color_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE color_product ADD CONSTRAINT FK_80DC89834584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE color_product ADD CONSTRAINT FK_80DC89837ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE color DROP slug');
        $this->addSql('ALTER TABLE length DROP slug');
    }
}
