<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201126214601 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_return ADD manager_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_return ADD CONSTRAINT FK_64112FDA783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_64112FDA783E3463 ON order_return (manager_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_return DROP FOREIGN KEY FK_64112FDA783E3463');
        $this->addSql('DROP INDEX IDX_64112FDA783E3463 ON order_return');
        $this->addSql('ALTER TABLE order_return DROP manager_id');
    }
}
