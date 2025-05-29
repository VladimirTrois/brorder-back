<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250315175653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergy (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBB142B55E237E06 ON allergy (name)');
        $this->addSql('CREATE TABLE product_allergy (id SERIAL NOT NULL, product_id INT NOT NULL, allergy_id INT NOT NULL, level VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1A6859054584665A ON product_allergy (product_id)');
        $this->addSql('CREATE INDEX IDX_1A685905DBFD579D ON product_allergy (allergy_id)');
        $this->addSql('ALTER TABLE product_allergy ADD CONSTRAINT FK_1A6859054584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_allergy ADD CONSTRAINT FK_1A685905DBFD579D FOREIGN KEY (allergy_id) REFERENCES allergy (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_allergy DROP CONSTRAINT FK_1A6859054584665A');
        $this->addSql('ALTER TABLE product_allergy DROP CONSTRAINT FK_1A685905DBFD579D');
        $this->addSql('DROP TABLE allergy');
        $this->addSql('DROP TABLE product_allergy');
    }
}
