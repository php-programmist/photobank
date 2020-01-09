<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200109134221 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE batch CHANGE type_id type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D4C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('CREATE INDEX IDX_F80B52D4C54C8C93 ON batch (type_id)');
        $this->addSql('ALTER TABLE type CHANGE name name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE batch DROP FOREIGN KEY FK_F80B52D4C54C8C93');
        $this->addSql('DROP INDEX IDX_F80B52D4C54C8C93 ON batch');
        $this->addSql('ALTER TABLE batch CHANGE type_id type_id INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE type CHANGE name name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
