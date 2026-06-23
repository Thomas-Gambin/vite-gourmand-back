<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adresse de prestation sur commande.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande ADD adresse_prestation VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE commande ADD ville_prestation VARCHAR(50) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE commande ADD code_postal_prestation VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ALTER COLUMN adresse_prestation DROP DEFAULT');
        $this->addSql('ALTER TABLE commande ALTER COLUMN ville_prestation DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande DROP adresse_prestation');
        $this->addSql('ALTER TABLE commande DROP ville_prestation');
        $this->addSql('ALTER TABLE commande DROP code_postal_prestation');
    }
}
