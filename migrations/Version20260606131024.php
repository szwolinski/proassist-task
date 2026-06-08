<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260606131024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT fk_97a0ada35cf77dd3');
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT fk_97a0ada394a4c7d4');
        $this->addSql('ALTER TABLE ticket ALTER device_id DROP NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA35CF77DD3 FOREIGN KEY (assigned_technician_id) REFERENCES technician (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA394A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA35CF77DD3');
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA394A4C7D4');
        $this->addSql('ALTER TABLE ticket ALTER device_id SET NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT fk_97a0ada35cf77dd3 FOREIGN KEY (assigned_technician_id) REFERENCES technician (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT fk_97a0ada394a4c7d4 FOREIGN KEY (device_id) REFERENCES device (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
