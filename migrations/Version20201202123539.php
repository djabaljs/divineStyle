<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202123539 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD253C865B');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD4679B87C');
        $this->addSql('DROP INDEX IDX_D34A04AD4679B87C ON product');
        $this->addSql('DROP INDEX IDX_D34A04AD253C865B ON product');
        $this->addSql('ALTER TABLE product DROP width_id, DROP height_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD width_id INT DEFAULT NULL, ADD height_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD253C865B FOREIGN KEY (width_id) REFERENCES width (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4679B87C FOREIGN KEY (height_id) REFERENCES height (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD4679B87C ON product (height_id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD253C865B ON product (width_id)');
    }
}
