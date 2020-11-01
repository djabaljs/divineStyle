<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201029205353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD4A7E4868');
        $this->addSql('DROP INDEX IDX_D34A04AD4A7E4868 ON product');
        $this->addSql('ALTER TABLE product DROP sale_id');
        $this->addSql('ALTER TABLE sale ADD products_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC0056C8A81A9 FOREIGN KEY (products_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_E54BC0056C8A81A9 ON sale (products_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD sale_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4A7E4868 FOREIGN KEY (sale_id) REFERENCES sale (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD4A7E4868 ON product (sale_id)');
        $this->addSql('ALTER TABLE sale DROP FOREIGN KEY FK_E54BC0056C8A81A9');
        $this->addSql('DROP INDEX IDX_E54BC0056C8A81A9 ON sale');
        $this->addSql('ALTER TABLE sale DROP products_id');
    }
}
