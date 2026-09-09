<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260904094623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute un index FULLTEXT sur knowledge_article pour la recherche';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE knowledge_article ADD FULLTEXT INDEX idx_fulltext_search (title, problem, solution)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE knowledge_article DROP INDEX idx_fulltext_search');
    }
}
