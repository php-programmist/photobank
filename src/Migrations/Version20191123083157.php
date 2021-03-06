<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191123083157 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, batch_id INT NOT NULL, path VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_14B78418F39EBE7A (batch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418F39EBE7A FOREIGN KEY (batch_id) REFERENCES batch (id)');
        $this->addSql('ALTER TABLE batch ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F80B52D4A76ED395 ON batch (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE photo');
        $this->addSql('ALTER TABLE batch DROP FOREIGN KEY FK_F80B52D4A76ED395');
        $this->addSql('DROP INDEX IDX_F80B52D4A76ED395 ON batch');
        $this->addSql('ALTER TABLE batch DROP user_id');
    }
}
