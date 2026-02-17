<?php

namespace App\Entity;

use App\Repository\RankingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingsRepository::class)]
#[ORM\Table(name: 'rankings')]
#[ORM\UniqueConstraint(name: 'usuario_categoria', columns: ['id_usuario', 'id_categoria'])] // 👈 NUEVO: evita duplicados
class Rankings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class, inversedBy: 'rankings')]
    #[ORM\JoinColumn(name: 'id_usuario', nullable: false, onDelete: 'CASCADE')]
    private ?Usuarios $usuario = null;

    // 👇 NUEVO: relación con categoría
    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(name: 'id_categoria', nullable: false)]
    private ?Categorias $categoria = null;

    #[ORM\Column(length: 100)]
    private ?string $nombreRanking = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $publico = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $creadoEn = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $actualizadoEn = null;

    #[ORM\OneToMany(targetEntity: RankingPeliculas::class, mappedBy: 'ranking', orphanRemoval: true)]
    #[ORM\OrderBy(['posicion' => 'ASC'])]
    private Collection $rankingPeliculas;

    public function __construct()
    {
        $this->rankingPeliculas = new ArrayCollection();
        $this->creadoEn = new \DateTimeImmutable();
        $this->actualizadoEn = new \DateTimeImmutable();
        $this->publico = true;
    }

    // ... todos los getters/setters existentes ...

    // 👇 NUEVO: getter y setter de categoría
    public function getCategoria(): ?Categorias
    {
        return $this->categoria;
    }

    public function setCategoria(?Categorias $categoria): static
    {
        $this->categoria = $categoria;
        return $this;
    }

    // ... resto de métodos existentes sin cambios ...
    public function getId(): ?int { return $this->id; }
    public function getUsuario(): ?Usuarios { return $this->usuario; }
    public function setUsuario(?Usuarios $usuario): static { $this->usuario = $usuario; return $this; }
    public function getNombreRanking(): ?string { return $this->nombreRanking; }
    public function setNombreRanking(string $nombreRanking): static { $this->nombreRanking = $nombreRanking; return $this; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function setDescripcion(?string $descripcion): static { $this->descripcion = $descripcion; return $this; }
    public function isPublico(): ?bool { return $this->publico; }
    public function setPublico(bool $publico): static { $this->publico = $publico; return $this; }
    public function getCreadoEn(): ?\DateTimeImmutable { return $this->creadoEn; }
    public function setCreadoEn(\DateTimeImmutable $creadoEn): static { $this->creadoEn = $creadoEn; return $this; }
    public function getActualizadoEn(): ?\DateTimeImmutable { return $this->actualizadoEn; }
    public function setActualizadoEn(\DateTimeImmutable $actualizadoEn): static { $this->actualizadoEn = $actualizadoEn; return $this; }
    public function getRankingPeliculas(): Collection { return $this->rankingPeliculas; }

    public function addRankingPelicula(RankingPeliculas $rankingPelicula): static
    {
        if (!$this->rankingPeliculas->contains($rankingPelicula)) {
            $this->rankingPeliculas->add($rankingPelicula);
            $rankingPelicula->setRanking($this);
        }
        return $this;
    }

    public function removeRankingPelicula(RankingPeliculas $rankingPelicula): static
    {
        if ($this->rankingPeliculas->removeElement($rankingPelicula)) {
            if ($rankingPelicula->getRanking() === $this) {
                $rankingPelicula->setRanking(null);
            }
        }
        return $this;
    }
}
