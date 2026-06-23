<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Distance de livraison calculée sur commande.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande ADD distance_livraison_km NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande DROP distance_livraison_km');
    }
}
