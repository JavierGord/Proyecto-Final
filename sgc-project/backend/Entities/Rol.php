<?php
declare(strict_types=1);

namespace App\Entities;

class Rol {
    private int $id;
    private string $nombre; // Ej: 'Administrador', 'Vendedor', 'Contador'

    public function __construct(int $id, string $nombre) {
        $this->id = $id;
        $this->nombre = $nombre;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
}
