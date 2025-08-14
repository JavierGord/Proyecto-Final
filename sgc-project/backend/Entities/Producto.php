<?php
declare(strict_types=1);

namespace App\Entities;

abstract class Producto {
    protected int $id;
    protected string $nombre;
    protected string $descripcion;
    protected float $precioUnitario;
    protected int $stock;
    protected int $idCategoria;
    protected bool $estado;
    protected \DateTime $fechaCreacion;

    public function __construct(
        int $id,
        string $nombre,
        string $descripcion,
        float $precioUnitario,
        int $stock,
        int $idCategoria,
        bool $estado,
        \DateTime $fechaCreacion
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precioUnitario = $precioUnitario;
        $this->stock = $stock;
        $this->idCategoria = $idCategoria;
        $this->estado = $estado;
        $this->fechaCreacion = $fechaCreacion;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getPrecioUnitario(): float { return $this->precioUnitario; }
    public function getStock(): int { return $this->stock; }
    public function getIdCategoria(): int { return $this->idCategoria; }
    public function getEstado(): bool { return $this->estado; }
    public function getFechaCreacion(): \DateTime { return $this->fechaCreacion; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }
    public function setPrecioUnitario(float $precioUnitario): void { $this->precioUnitario = $precioUnitario; }
    public function setStock(int $stock): void { $this->stock = $stock; }
    public function setIdCategoria(int $idCategoria): void { $this->idCategoria = $idCategoria; }
    public function setEstado(bool $estado): void { $this->estado = $estado; }
    public function setFechaCreacion(\DateTime $fechaCreacion): void { $this->fechaCreacion = $fechaCreacion; }
}
