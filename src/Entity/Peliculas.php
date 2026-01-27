<?php

namespace App\Entity;

use App\Repository\PeliculasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PeliculasRepository::class)]
#[ORM\Table(name: 'peliculas')]
class Peliculas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36)]
    private ?string $ghibliId = null;

    #[ORM\Column(length: 255)]
    private ?string $titulo = null;

    #[ORM\Column(length: 100)]
    private ?string $director = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $productor = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $anoLanzamiento = null;

    #[ORM\Column(nullable: true)]
    private ?int $duracion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagenUrl = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private ?string $promedioRating = '0.00';

    #[ORM\Column]
    private ?int $contadorRating = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $creadoEn = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $actualizadoEn = null;

    #[ORM\OneToMany(targetEntity: Valoraciones::class, mappedBy: 'pelicula', orphanRemoval: true)]
    private Collection $valoraciones;

    #[ORM\OneToMany(targetEntity: RankingPeliculas::class, mappedBy: 'pelicula', orphanRemoval: true)]
    private Collection $rankingPeliculas;

    public function __construct()
    {
        $this->valoraciones = new ArrayCollection();
        $this->rankingPeliculas = new ArrayCollection();
        $this->creadoEn = new \DateTimeImmutable();
        $this->actualizadoEn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGhibliId(): ?string
    {
        return $this->ghibliId;
    }

    public function setGhibliId(string $ghibliId): static
    {
        $this->ghibliId = $ghibliId;
        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(string $director): static
    {
        $this->director = $director;
        return $this;
    }

    public function getProductor(): ?string
    {
        return $this->productor;
    }

    public function setProductor(?string $productor): static
    {
        $this->productor = $productor;
        return $this;
    }

    public function getAnoLanzamiento(): ?int
    {
        return $this->anoLanzamiento;
    }

    public function setAnoLanzamiento(?int $anoLanzamiento): static
    {
        $this->anoLanzamiento = $anoLanzamiento;
        return $this;
    }

    public function getDuracion(): ?int
    {
        return $this->duracion;
    }

    public function setDuracion(?int $duracion): static
    {
        $this->duracion = $duracion;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getImagenUrl(): ?string
    {
        return $this->imagenUrl;
    }

    public function setImagenUrl(?string $imagenUrl): static
    {
        $this->imagenUrl = $imagenUrl;
        return $this;
    }

    public function getPromedioRating(): ?float
    {
        return $this->promedioRating ? (float) $this->promedioRating : 0.0;
    }

    public function setPromedioRating(string $promedioRating): static
    {
        $this->promedioRating = $promedioRating;
        return $this;
    }

    public function getContadorRating(): ?int
    {
        return $this->contadorRating;
    }

    public function setContadorRating(int $contadorRating): static
    {
        $this->contadorRating = $contadorRating;
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

    /**
     * @return Collection<int, Valoraciones>
     */
    public function getValoraciones(): Collection
    {
        return $this->valoraciones;
    }

    public function addValoracione(Valoraciones $valoracione): static
    {
        if (!$this->valoraciones->contains($valoracione)) {
            $this->valoraciones->add($valoracione);
            $valoracione->setPelicula($this);
        }
        return $this;
    }

    public function removeValoracione(Valoraciones $valoracione): static
    {
        if ($this->valoraciones->removeElement($valoracione)) {
            if ($valoracione->getPelicula() === $this) {
                $valoracione->setPelicula(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, RankingPeliculas>
     */
    public function getRankingPeliculas(): Collection
    {
        return $this->rankingPeliculas;
    }

    public function addRankingPelicula(RankingPeliculas $rankingPelicula): static
    {
        if (!$this->rankingPeliculas->contains($rankingPelicula)) {
            $this->rankingPeliculas->add($rankingPelicula);
            $rankingPelicula->setPelicula($this);
        }
        return $this;
    }

    public function removeRankingPelicula(RankingPeliculas $rankingPelicula): static
    {
        if ($this->rankingPeliculas->removeElement($rankingPelicula)) {
            if ($rankingPelicula->getPelicula() === $this) {
                $rankingPelicula->setPelicula(null);
            }
        }
        return $this;
    }
}
