<?php

namespace App\Entity;

use App\Enum\RolEnum;
use App\Repository\UsuariosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuariosRepository::class)]
#[ORM\Table(name: 'usuarios')]
#[UniqueEntity(fields: ['correoElectronico'], message: 'Ya existe una cuenta con este correo electrónico')]
class Usuarios implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nombreUsuario = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $correoElectronico = null;

    #[ORM\Column(length: 255)]
    private ?string $contrasena = null;

    #[ORM\Column(type: 'string', enumType: RolEnum::class)]
    private RolEnum $rol = RolEnum::USER;

    #[ORM\Column]
    private ?\DateTimeImmutable $creadoEn = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $actualizadoEn = null;

    #[ORM\OneToMany(targetEntity: Valoraciones::class, mappedBy: 'usuario', orphanRemoval: true)]
    private Collection $valoraciones;

    #[ORM\OneToMany(targetEntity: Rankings::class, mappedBy: 'usuario', orphanRemoval: true)]
    private Collection $rankings;

    public function __construct()
    {
        $this->valoraciones = new ArrayCollection();
        $this->rankings = new ArrayCollection();
        $this->creadoEn = new \DateTimeImmutable();
        $this->actualizadoEn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombreUsuario;
    }

    public function setNombreUsuario(string $nombreUsuario): static
    {
        $this->nombreUsuario = $nombreUsuario;
        return $this;
    }

    public function getCorreoElectronico(): ?string
    {
        return $this->correoElectronico;
    }

    public function setCorreoElectronico(string $correoElectronico): static
    {
        $this->correoElectronico = $correoElectronico;
        return $this;
    }

    public function getContrasena(): ?string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): static
    {
        $this->contrasena = $contrasena;
        return $this;
    }

    public function getRol(): RolEnum
    {
        return $this->rol;
    }

    public function setRol(RolEnum $rol): static
    {
        $this->rol = $rol;
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->rol === RolEnum::ADMIN;
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
            $valoracione->setUsuario($this);
        }
        return $this;
    }

    public function removeValoracione(Valoraciones $valoracione): static
    {
        if ($this->valoraciones->removeElement($valoracione)) {
            if ($valoracione->getUsuario() === $this) {
                $valoracione->setUsuario(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Rankings>
     */
    public function getRankings(): Collection
    {
        return $this->rankings;
    }

    public function addRanking(Rankings $ranking): static
    {
        if (!$this->rankings->contains($ranking)) {
            $this->rankings->add($ranking);
            $ranking->setUsuario($this);
        }
        return $this;
    }

    public function removeRanking(Rankings $ranking): static
    {
        if ($this->rankings->removeElement($ranking)) {
            if ($ranking->getUsuario() === $this) {
                $ranking->setUsuario(null);
            }
        }
        return $this;
    }

    // ========== MÉTODOS REQUERIDOS POR UserInterface ==========

    /**
     * El identificador único para el usuario (usado por Symfony Security)
     */
    public function getUserIdentifier(): string
    {
        return $this->correoElectronico;
    }

    /**
     * Los roles del usuario (devuelve array de roles)
     */
    public function getRoles(): array
    {
        // Garantiza que cada usuario tenga al menos ROLE_USER
        $roles = ['ROLE_USER'];

        // Si es admin, añade ROLE_ADMIN
        if ($this->rol === RolEnum::ADMIN) {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }

    /**
     * Borra las credenciales sensibles (no necesario aquí)
     */
    public function eraseCredentials(): void
    {
        // Si almacenaras un plainPassword temporal, lo borrarías aquí
    }

    /**
     * Devuelve la contraseña hasheada
     */
    public function getPassword(): ?string
    {
        return $this->contrasena;
    }
}
