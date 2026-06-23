<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Lien avis-commande et historique des statuts de commande.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE avis ADD commande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8B918D6E82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B918D6E82EA2E54 ON avis (commande_id)');

        $this->addSql('CREATE TABLE commande_historique_statut (id SERIAL NOT NULL, commande_id INT NOT NULL, statut VARCHAR(50) NOT NULL, date_modification TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_commande_historique_commande ON commande_historique_statut (commande_id)');
        $this->addSql('ALTER TABLE commande_historique_statut ADD CONSTRAINT FK_commande_historique_commande FOREIGN KEY (commande_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande_historique_statut DROP CONSTRAINT FK_commande_historique_commande');
        $this->addSql('DROP TABLE commande_historique_statut');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8B918D6E82EA2E54');
        $this->addSql('DROP INDEX UNIQ_8B918D6E82EA2E54');
        $this->addSql('ALTER TABLE avis DROP commande_id');
    }
}
