<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191125062725 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE batch ADD brand_id INT NOT NULL, ADD service_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D444F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D4DEDCBB4E FOREIGN KEY (service_category_id) REFERENCES service_category (id)');
        $this->addSql('CREATE INDEX IDX_F80B52D444F5D008 ON batch (brand_id)');
        $this->addSql('CREATE INDEX IDX_F80B52D4DEDCBB4E ON batch (service_category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE batch DROP FOREIGN KEY FK_F80B52D444F5D008');
        $this->addSql('ALTER TABLE batch DROP FOREIGN KEY FK_F80B52D4DEDCBB4E');
        $this->addSql('DROP INDEX IDX_F80B52D444F5D008 ON batch');
        $this->addSql('DROP INDEX IDX_F80B52D4DEDCBB4E ON batch');
        $this->addSql('ALTER TABLE batch DROP brand_id, DROP service_category_id');
    }
}
