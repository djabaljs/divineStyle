<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202220135 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE color DROP FOREIGN KEY FK_665648E9DC269DB3');
        $this->addSql('DROP INDEX IDX_665648E9DC269DB3 ON color');
        $this->addSql('ALTER TABLE color DROP product_variation_id');
        $this->addSql('ALTER TABLE product_variation ADD colors_id INT DEFAULT NULL, ADD lengths_id INT DEFAULT NULL, ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85675C002039 FOREIGN KEY (colors_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85676CAEE88A FOREIGN KEY (lengths_id) REFERENCES length (id)');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85674584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_C3B85675C002039 ON product_variation (colors_id)');
        $this->addSql('CREATE INDEX IDX_C3B85676CAEE88A ON product_variation (lengths_id)');
        $this->addSql('CREATE INDEX IDX_C3B85674584665A ON product_variation (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE color ADD product_variation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE color ADD CONSTRAINT FK_665648E9DC269DB3 FOREIGN KEY (product_variation_id) REFERENCES product_variation (id)');
        $this->addSql('CREATE INDEX IDX_665648E9DC269DB3 ON color (product_variation_id)');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85675C002039');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85676CAEE88A');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85674584665A');
        $this->addSql('DROP INDEX IDX_C3B85675C002039 ON product_variation');
        $this->addSql('DROP INDEX IDX_C3B85676CAEE88A ON product_variation');
        $this->addSql('DROP INDEX IDX_C3B85674584665A ON product_variation');
        $this->addSql('ALTER TABLE product_variation DROP colors_id, DROP lengths_id, DROP product_id');
    }
}
