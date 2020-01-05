<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191225205351 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activity ADD worker_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A6B20BA36 FOREIGN KEY (worker_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AC74095A6B20BA36 ON activity (worker_id)');
        $this->addSql('ALTER TABLE period CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A6B20BA36');
        $this->addSql('DROP INDEX IDX_AC74095A6B20BA36 ON activity');
        $this->addSql('ALTER TABLE activity DROP worker_id');
        $this->addSql('ALTER TABLE period CHANGE created_at created_at DATETIME DEFAULT NULL');
    }
}
