<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260831125545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE knowledge_article (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, problem LONGTEXT NOT NULL, cause LONGTEXT DEFAULT NULL, solution LONGTEXT NOT NULL, category VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, author_id INT NOT NULL, INDEX IDX_7D7D20E9F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE knowledge_article ADD CONSTRAINT FK_7D7D20E9F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE knowledge_article DROP FOREIGN KEY FK_7D7D20E9F675F31B');
        $this->addSql('DROP TABLE knowledge_article');
    }
}
