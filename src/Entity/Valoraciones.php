<?php

namespace App\Entity;

use App\Repository\ValoracionesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValoracionesRepository::class)]
#[ORM\Table(name: 'valoraciones')]
#[ORM\UniqueConstraint(name: 'usuario_pelicula', columns: ['id_usuario', 'id_pelicula'])]
class Valoraciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class, inversedBy: 'valoraciones')]
    #[ORM\JoinColumn(name: 'id_usuario', nullable: false, onDelete: 'CASCADE')]
    private ?Usuarios $usuario = null;

    #[ORM\ManyToOne(targetEntity: Peliculas::class, inversedBy: 'valoraciones')]
    #[ORM\JoinColumn(name: 'id_pelicula', nullable: false, onDelete: 'CASCADE')]
    private ?Peliculas $pelicula = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private ?string $puntuacion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comentario = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $creadoEn = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $actualizadoEn = null;

    public function __construct()
    {
        $this->creadoEn = new \DateTimeImmutable();
        $this->actualizadoEn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuarios $usuario): static
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getPelicula(): ?Peliculas
    {
        return $this->pelicula;
    }

    public function setPelicula(?Peliculas $pelicula): static
    {
        $this->pelicula = $pelicula;
        return $this;
    }

    public function getPuntuacion(): ?float
    {
        return $this->puntuacion ? (float) $this->puntuacion : null;
    }

    public function setPuntuacion(string $puntuacion): static
    {
        $this->puntuacion = $puntuacion;
        return $this;
    }

    public function getComentario(): ?string
    {
        return $this->comentario;
    }

    public function setComentario(?string $comentario): static
    {
        $this->comentario = $comentario;
        return $this;
    }

    public function getCreadoEn(): ?\DateTimeImmutable
    {
        return $this->creadoEn;
    }

    public function setCreadoEn(\DateTimeImmutable $creadoEn): static
    {
        $this->creadoEn = $creadoEn;
        return $this;
    }

    public function getActualizadoEn(): ?\DateTimeImmutable
    {
        return $this->actualizadoEn;
    }

    public function setActualizadoEn(\DateTimeImmutable $actualizadoEn): static
    {
        $this->actualizadoEn = $actualizadoEn;
        return $this;
    }
}
