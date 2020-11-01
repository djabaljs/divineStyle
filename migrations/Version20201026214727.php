<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201026214727 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop DROP FOREIGN KEY FK_AC6A4CA22A94E7F');
        $this->addSql('DROP INDEX IDX_AC6A4CA22A94E7F ON shop');
        $this->addSql('ALTER TABLE shop DROP staffs_id');
        $this->addSql('ALTER TABLE staff ADD shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE staff ADD CONSTRAINT FK_426EF3924D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_426EF3924D16C4DD ON staff (shop_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop ADD staffs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA22A94E7F FOREIGN KEY (staffs_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AC6A4CA22A94E7F ON shop (staffs_id)');
        $this->addSql('ALTER TABLE staff DROP FOREIGN KEY FK_426EF3924D16C4DD');
        $this->addSql('DROP INDEX IDX_426EF3924D16C4DD ON staff');
        $this->addSql('ALTER TABLE staff DROP shop_id');
    }
}
