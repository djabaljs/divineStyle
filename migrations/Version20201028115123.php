<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201028115123 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494D16C4DD');
        $this->addSql('DROP INDEX IDX_8D93D6494D16C4DD ON user');
        $this->addSql('ALTER TABLE user CHANGE shop_id shops_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491F5E0588 FOREIGN KEY (shops_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491F5E0588 ON user (shops_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491F5E0588');
        $this->addSql('DROP INDEX IDX_8D93D6491F5E0588 ON user');
        $this->addSql('ALTER TABLE user CHANGE shops_id shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6494D16C4DD ON user (shop_id)');
    }
}
