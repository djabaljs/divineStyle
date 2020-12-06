<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202220540 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85675C002039');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85676CAEE88A');
        $this->addSql('DROP INDEX IDX_C3B85676CAEE88A ON product_variation');
        $this->addSql('DROP INDEX IDX_C3B85675C002039 ON product_variation');
        $this->addSql('ALTER TABLE product_variation ADD color_id INT DEFAULT NULL, ADD length_id INT DEFAULT NULL, ADD quantity INT NOT NULL, DROP colors_id, DROP lengths_id');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85677ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B856761ED455A FOREIGN KEY (length_id) REFERENCES length (id)');
        $this->addSql('CREATE INDEX IDX_C3B85677ADA1FB5 ON product_variation (color_id)');
        $this->addSql('CREATE INDEX IDX_C3B856761ED455A ON product_variation (length_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B85677ADA1FB5');
        $this->addSql('ALTER TABLE product_variation DROP FOREIGN KEY FK_C3B856761ED455A');
        $this->addSql('DROP INDEX IDX_C3B85677ADA1FB5 ON product_variation');
        $this->addSql('DROP INDEX IDX_C3B856761ED455A ON product_variation');
        $this->addSql('ALTER TABLE product_variation ADD colors_id INT DEFAULT NULL, ADD lengths_id INT DEFAULT NULL, DROP color_id, DROP length_id, DROP quantity');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85675C002039 FOREIGN KEY (colors_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE product_variation ADD CONSTRAINT FK_C3B85676CAEE88A FOREIGN KEY (lengths_id) REFERENCES length (id)');
        $this->addSql('CREATE INDEX IDX_C3B85676CAEE88A ON product_variation (lengths_id)');
        $this->addSql('CREATE INDEX IDX_C3B85675C002039 ON product_variation (colors_id)');
    }
}
