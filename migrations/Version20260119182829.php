<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119182829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE peliculas (id INT AUTO_INCREMENT NOT NULL, ghibli_id INT NOT NULL, titulo VARCHAR(255) DEFAULT NULL, director VARCHAR(255) DEFAULT NULL, productor VARCHAR(255) DEFAULT NULL, ano_lanzamiento DATETIME DEFAULT NULL, duracion INT DEFAULT NULL, descripcion LONGTEXT DEFAULT NULL, imagen_url VARCHAR(255) DEFAULT NULL, promedio_rating NUMERIC(10, 0) DEFAULT NULL, contador_rating INT DEFAULT NULL, creado_en DATETIME DEFAULT NULL, actualizado_en DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ranking_peliculas (id INT AUTO_INCREMENT NOT NULL, posicion INT DEFAULT NULL, nota_personal LONGTEXT DEFAULT NULL, creado_en DATETIME DEFAULT NULL, id_ranking INT NOT NULL, id_pelicula INT NOT NULL, INDEX IDX_86026B8D5E2590A0 (id_ranking), INDEX IDX_86026B8D23679C00 (id_pelicula), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rankings (id INT AUTO_INCREMENT NOT NULL, nombre_ranking VARCHAR(255) DEFAULT NULL, descripcion VARCHAR(255) DEFAULT NULL, publico TINYINT DEFAULT NULL, creado_en DATETIME DEFAULT NULL, actualizado_en DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE usuarios (id INT AUTO_INCREMENT NOT NULL, nombre_usuario VARCHAR(255) NOT NULL, correo_electronico VARCHAR(255) NOT NULL, contrasena VARCHAR(255) DEFAULT NULL, creado_en DATETIME DEFAULT NULL, actualizado_en DATETIME DEFAULT NULL, rol LONGTEXT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE valoraciones (id INT AUTO_INCREMENT NOT NULL, puntuacion NUMERIC(10, 0) DEFAULT NULL, comentario LONGTEXT DEFAULT NULL, creado_en DATETIME DEFAULT NULL, actualizado_en DATETIME DEFAULT NULL, id_usuario_id INT DEFAULT NULL, id_pelicula_id INT DEFAULT NULL, INDEX IDX_408506677EB2C349 (id_usuario_id), INDEX IDX_408506676863F4FE (id_pelicula_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE ranking_peliculas ADD CONSTRAINT FK_86026B8D5E2590A0 FOREIGN KEY (id_ranking) REFERENCES rankings (id)');
        $this->addSql('ALTER TABLE ranking_peliculas ADD CONSTRAINT FK_86026B8D23679C00 FOREIGN KEY (id_pelicula) REFERENCES peliculas (id)');
        $this->addSql('ALTER TABLE valoraciones ADD CONSTRAINT FK_408506677EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE valoraciones ADD CONSTRAINT FK_408506676863F4FE FOREIGN KEY (id_pelicula_id) REFERENCES peliculas (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranking_peliculas DROP FOREIGN KEY FK_86026B8D5E2590A0');
        $this->addSql('ALTER TABLE ranking_peliculas DROP FOREIGN KEY FK_86026B8D23679C00');
        $this->addSql('ALTER TABLE valoraciones DROP FOREIGN KEY FK_408506677EB2C349');
        $this->addSql('ALTER TABLE valoraciones DROP FOREIGN KEY FK_408506676863F4FE');
        $this->addSql('DROP TABLE peliculas');
        $this->addSql('DROP TABLE ranking_peliculas');
        $this->addSql('DROP TABLE rankings');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE valoraciones');
    }
}
