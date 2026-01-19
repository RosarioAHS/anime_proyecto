<?php

namespace App\Enum;

enum RolEnum: string
{
    case USER = 'USER';
    case ADMIN = 'ADMIN';

    /**
     * Obtiene todos los valores posibles
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Obtiene la etiqueta legible
     */
    public function label(): string
    {
        return match($this) {
            self::USER => 'Usuario',
            self::ADMIN => 'Administrador',
        };
    }

    /**
     * Verifica si es admin
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Verifica si es usuario normal
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }
}
