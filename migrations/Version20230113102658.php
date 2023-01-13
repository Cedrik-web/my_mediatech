<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230113102658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE network (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, follow_id INT DEFAULT NULL, groupe_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_608487BCA76ED395 (user_id), INDEX IDX_608487BC8711D3BC (follow_id), INDEX IDX_608487BC7A45358C (groupe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BC8711D3BC FOREIGN KEY (follow_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BC7A45358C FOREIGN KEY (groupe_id) REFERENCES `group` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BCA76ED395');
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BC8711D3BC');
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BC7A45358C');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE network');
    }
}
