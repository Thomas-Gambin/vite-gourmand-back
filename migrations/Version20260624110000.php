<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260624110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le type de plat et l\'unicité du jour pour les horaires';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE plat ADD type_plat VARCHAR(20) DEFAULT 'plat' NOT NULL");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_horaire_jour ON horaire (jour)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plat DROP type_plat');
        $this->addSql('DROP INDEX UNIQ_horaire_jour ON horaire');
    }

    public function postUp(Schema $schema): void
    {
        $types = ['entree', 'plat', 'dessert'];
        $rows = $this->connection->fetchAllAssociative(
            'SELECT menu_id, plat_id FROM menu_plat ORDER BY menu_id, plat_id'
        );

        $currentMenuId = null;
        $index = 0;
        foreach ($rows as $row) {
            $menuId = (int) $row['menu_id'];
            if ($menuId !== $currentMenuId) {
                $currentMenuId = $menuId;
                $index = 0;
            }

            $type = $types[$index] ?? 'plat';
            $this->connection->executeStatement(
                'UPDATE plat SET type_plat = ? WHERE id = ?',
                [$type, (int) $row['plat_id']]
            );
            ++$index;
        }
    }
}
