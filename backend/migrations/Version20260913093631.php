<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260913093631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne attachment_filename à la table comment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD attachment_filename VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP attachment_filename');
    }
}