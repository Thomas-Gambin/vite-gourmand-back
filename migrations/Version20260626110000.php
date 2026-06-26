<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalise les chemins photo des plats (nom de fichier uniquement)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE plat SET photo = REPLACE(photo, '/uploads/plats/', '') WHERE photo LIKE '/uploads/plats/%'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE plat SET photo = '/uploads/plats/' || photo WHERE photo IS NOT NULL AND photo <> '' AND photo NOT LIKE '/%'");
    }
}
