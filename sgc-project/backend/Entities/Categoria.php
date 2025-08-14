<?php
declare(strict_types=1);

namespace App\Entities;

class Categoria {
    private int $id;
    private string $nombre;
    private string $descripcion;
    private bool $estado;
    private \DateTime $fechaCreacion;

    public function __construct(
        int $id,
        string $nombre,
        string $descripcion,
        bool $estado,
        \DateTime $fechaCreacion
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->estado = $estado;
        $this->fechaCreacion = $fechaCreacion;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getEstado(): bool { return $this->estado; }
    public function getFechaCreacion(): \DateTime { return $this->fechaCreacion; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }
    public function setEstado(bool $estado): void { $this->estado = $estado; }
    public function setFechaCreacion(\DateTime $fechaCreacion): void { $this->fechaCreacion = $fechaCreacion; }
}
