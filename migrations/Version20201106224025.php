<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106224025 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sale');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD8ED1E5CA');
        $this->addSql('DROP INDEX IDX_D34A04AD8ED1E5CA ON product');
        $this->addSql('ALTER TABLE product DROP product_option_type_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, manager_id INT NOT NULL, customer_id INT NOT NULL, number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, sale_vat_price DOUBLE PRECISION NOT NULL, sale_total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, product_number INT NOT NULL, INDEX IDX_E54BC005783E3463 (manager_id), INDEX IDX_E54BC0059395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC005783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC0059395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE product ADD product_option_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD8ED1E5CA FOREIGN KEY (product_option_type_id) REFERENCES product_option_type (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD8ED1E5CA ON product (product_option_type_id)');
    }
}
