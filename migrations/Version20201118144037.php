<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201118144037 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398F815A66A');
        $this->addSql('DROP INDEX UNIQ_F5299398F815A66A ON `order`');
        $this->addSql('ALTER TABLE `order` DROP deliveries_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD deliveries_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398F815A66A FOREIGN KEY (deliveries_id) REFERENCES delivery (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5299398F815A66A ON `order` (deliveries_id)');
    }
}
