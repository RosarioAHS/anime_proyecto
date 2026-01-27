<?php

namespace App\Entity;

use App\Repository\RankingPeliculasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingPeliculasRepository::class)]
#[ORM\Table(name: 'ranking_peliculas')]
#[ORM\UniqueConstraint(name: 'ranking_pelicula', columns: ['id_ranking', 'id_pelicula'])]
class RankingPeliculas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Rankings::class, inversedBy: 'rankingPeliculas')]
    #[ORM\JoinColumn(name: 'id_ranking', nullable: false, onDelete: 'CASCADE')]
    private ?Rankings $ranking = null;

    #[ORM\ManyToOne(targetEntity: Peliculas::class, inversedBy: 'rankingPeliculas')]
    #[ORM\JoinColumn(name: 'id_pelicula', nullable: false, onDelete: 'CASCADE')]
    private ?Peliculas $pelicula = null;

    #[ORM\Column]
    private ?int $posicion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notaPersonal = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $creadoEn = null;

    public function __construct()
    {
        $this->creadoEn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRanking(): ?Rankings
    {
        return $this->ranking;
    }

    public function setRanking(?Rankings $ranking): static
    {
        $this->ranking = $ranking;
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

    public function getPosicion(): ?int
    {
        return $this->posicion;
    }

    public function setPosicion(int $posicion): static
    {
        $this->posicion = $posicion;
        return $this;
    }

    public function getNotaPersonal(): ?string
    {
        return $this->notaPersonal;
    }

    public function setNotaPersonal(?string $notaPersonal): static
    {
        $this->notaPersonal = $notaPersonal;
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
}
