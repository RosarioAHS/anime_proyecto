<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120121109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE peliculas CHANGE ghibli_id ghibli_id VARCHAR(36) NOT NULL, CHANGE titulo titulo VARCHAR(255) NOT NULL, CHANGE director director VARCHAR(100) NOT NULL, CHANGE productor productor VARCHAR(100) DEFAULT NULL, CHANGE ano_lanzamiento ano_lanzamiento SMALLINT DEFAULT NULL, CHANGE promedio_rating promedio_rating NUMERIC(3, 2) NOT NULL, CHANGE contador_rating contador_rating INT NOT NULL, CHANGE creado_en creado_en DATETIME NOT NULL, CHANGE actualizado_en actualizado_en DATETIME NOT NULL');
        $this->addSql('ALTER TABLE usuarios CHANGE nombre_usuario nombre_usuario VARCHAR(50) NOT NULL, CHANGE correo_electronico correo_electronico VARCHAR(100) NOT NULL, CHANGE contrasena contrasena VARCHAR(255) NOT NULL, CHANGE creado_en creado_en DATETIME NOT NULL, CHANGE actualizado_en actualizado_en DATETIME NOT NULL, CHANGE rol rol VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF687F2AE7A59DA ON usuarios (correo_electronico)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE peliculas CHANGE ghibli_id ghibli_id INT NOT NULL, CHANGE titulo titulo VARCHAR(255) DEFAULT NULL, CHANGE director director VARCHAR(255) DEFAULT NULL, CHANGE productor productor VARCHAR(255) DEFAULT NULL, CHANGE ano_lanzamiento ano_lanzamiento DATETIME DEFAULT NULL, CHANGE promedio_rating promedio_rating NUMERIC(10, 0) DEFAULT NULL, CHANGE contador_rating contador_rating INT DEFAULT NULL, CHANGE creado_en creado_en DATETIME DEFAULT NULL, CHANGE actualizado_en actualizado_en DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_EF687F2AE7A59DA ON usuarios');
        $this->addSql('ALTER TABLE usuarios CHANGE nombre_usuario nombre_usuario VARCHAR(255) NOT NULL, CHANGE correo_electronico correo_electronico VARCHAR(255) NOT NULL, CHANGE contrasena contrasena VARCHAR(255) DEFAULT NULL, CHANGE rol rol LONGTEXT NOT NULL, CHANGE creado_en creado_en DATETIME DEFAULT NULL, CHANGE actualizado_en actualizado_en DATETIME DEFAULT NULL');
    }
}
