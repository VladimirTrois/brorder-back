<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321202423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_allergy DROP CONSTRAINT FK_1A685905DBFD579D');
        $this->addSql('ALTER TABLE product_allergy ADD CONSTRAINT FK_1A685905DBFD579D FOREIGN KEY (allergy_id) REFERENCES allergy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_allergy DROP CONSTRAINT fk_1a685905dbfd579d');
        $this->addSql('ALTER TABLE product_allergy ADD CONSTRAINT fk_1a685905dbfd579d FOREIGN KEY (allergy_id) REFERENCES allergy (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
