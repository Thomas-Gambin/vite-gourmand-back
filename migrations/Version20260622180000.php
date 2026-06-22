<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260622180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la vérification email sur utilisateur et seed le rôle ROLE_USER';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur ADD is_verified BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD email_verification_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD email_verification_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql("INSERT INTO role (libelle) SELECT 'ROLE_USER' WHERE NOT EXISTS (SELECT 1 FROM role WHERE libelle = 'ROLE_USER')");
        $this->addSql("INSERT INTO role (libelle) SELECT 'ROLE_ADMIN' WHERE NOT EXISTS (SELECT 1 FROM role WHERE libelle = 'ROLE_ADMIN')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur DROP is_verified');
        $this->addSql('ALTER TABLE utilisateur DROP email_verification_token');
        $this->addSql('ALTER TABLE utilisateur DROP email_verification_token_expires_at');
        $this->addSql('ALTER TABLE utilisateur DROP verified_at');
    }
}
