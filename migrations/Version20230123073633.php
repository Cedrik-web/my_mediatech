<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230123073633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE for_why (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_4997E197A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE for_why ADD CONSTRAINT FK_4997E197A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE album ADD for_why_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43B713AF46 FOREIGN KEY (for_why_id) REFERENCES for_why (id)');
        $this->addSql('CREATE INDEX IDX_39986E43B713AF46 ON album (for_why_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E43B713AF46');
        $this->addSql('ALTER TABLE for_why DROP FOREIGN KEY FK_4997E197A76ED395');
        $this->addSql('DROP TABLE for_why');
        $this->addSql('DROP INDEX IDX_39986E43B713AF46 ON album');
        $this->addSql('ALTER TABLE album DROP for_why_id');
    }
}
