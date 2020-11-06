<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201105214515 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sale');
        $this->addSql('ALTER TABLE color ADD register_id INT NOT NULL');
        $this->addSql('ALTER TABLE color ADD CONSTRAINT FK_665648E94976CB7E FOREIGN KEY (register_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_665648E94976CB7E ON color (register_id)');
        $this->addSql('ALTER TABLE height ADD register_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE height ADD CONSTRAINT FK_F54DE50F4976CB7E FOREIGN KEY (register_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F54DE50F4976CB7E ON height (register_id)');
        $this->addSql('ALTER TABLE length ADD register_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE length ADD CONSTRAINT FK_17D9EB24976CB7E FOREIGN KEY (register_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_17D9EB24976CB7E ON length (register_id)');
        $this->addSql('ALTER TABLE width ADD register_id INT NOT NULL');
        $this->addSql('ALTER TABLE width ADD CONSTRAINT FK_8C1A452F4976CB7E FOREIGN KEY (register_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8C1A452F4976CB7E ON width (register_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, manager_id INT NOT NULL, customer_id INT NOT NULL, number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, sale_vat_price DOUBLE PRECISION NOT NULL, sale_total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, product_number INT NOT NULL, INDEX IDX_E54BC005783E3463 (manager_id), INDEX IDX_E54BC0059395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC005783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC0059395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE color DROP FOREIGN KEY FK_665648E94976CB7E');
        $this->addSql('DROP INDEX IDX_665648E94976CB7E ON color');
        $this->addSql('ALTER TABLE color DROP register_id');
        $this->addSql('ALTER TABLE height DROP FOREIGN KEY FK_F54DE50F4976CB7E');
        $this->addSql('DROP INDEX IDX_F54DE50F4976CB7E ON height');
        $this->addSql('ALTER TABLE height DROP register_id');
        $this->addSql('ALTER TABLE length DROP FOREIGN KEY FK_17D9EB24976CB7E');
        $this->addSql('DROP INDEX IDX_17D9EB24976CB7E ON length');
        $this->addSql('ALTER TABLE length DROP register_id');
        $this->addSql('ALTER TABLE width DROP FOREIGN KEY FK_8C1A452F4976CB7E');
        $this->addSql('DROP INDEX IDX_8C1A452F4976CB7E ON width');
        $this->addSql('ALTER TABLE width DROP register_id');
    }
}
