<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201109152118 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sale');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE64584665A');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE68D9F6D38');
        $this->addSql('DROP INDEX IDX_2530ADE64584665A ON order_product');
        $this->addSql('DROP INDEX IDX_2530ADE68D9F6D38 ON order_product');
        $this->addSql('ALTER TABLE order_product ADD id INT AUTO_INCREMENT NOT NULL, ADD product_order_id INT NOT NULL, ADD products_id INT DEFAULT NULL, ADD quantity INT NOT NULL, DROP order_id, DROP product_id, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE6462F07AF FOREIGN KEY (product_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE66C8A81A9 FOREIGN KEY (products_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_2530ADE6462F07AF ON order_product (product_order_id)');
        $this->addSql('CREATE INDEX IDX_2530ADE66C8A81A9 ON order_product (products_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, manager_id INT NOT NULL, customer_id INT NOT NULL, number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, sale_vat_price DOUBLE PRECISION NOT NULL, sale_total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, product_number INT NOT NULL, INDEX IDX_E54BC005783E3463 (manager_id), INDEX IDX_E54BC0059395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC005783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC0059395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE order_product MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE6462F07AF');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE66C8A81A9');
        $this->addSql('DROP INDEX IDX_2530ADE6462F07AF ON order_product');
        $this->addSql('DROP INDEX IDX_2530ADE66C8A81A9 ON order_product');
        $this->addSql('ALTER TABLE order_product DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE order_product ADD order_id INT NOT NULL, ADD product_id INT NOT NULL, DROP id, DROP product_order_id, DROP products_id, DROP quantity');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE64584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE68D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2530ADE64584665A ON order_product (product_id)');
        $this->addSql('CREATE INDEX IDX_2530ADE68D9F6D38 ON order_product (order_id)');
        $this->addSql('ALTER TABLE order_product ADD PRIMARY KEY (order_id, product_id)');
    }
}
