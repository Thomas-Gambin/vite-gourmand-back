<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260624100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les champs de contact employé sur la table commande';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande ADD contact_mode VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD employee_action_reason TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD contacted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande DROP contact_mode');
        $this->addSql('ALTER TABLE commande DROP employee_action_reason');
        $this->addSql('ALTER TABLE commande DROP contacted_at');
    }
}
