<?php
declare(strict_types=1);

namespace App\Entities;

abstract class Cliente {
    protected int $id;
    protected string $email;
    protected string $telefono;
    protected string $direccion;
    protected bool $estado;
    protected \DateTime $fechaCreacion;

    public function __construct(
        int $id,
        string $email,
        string $telefono,
        string $direccion,
        bool $estado,
        \DateTime $fechaCreacion
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->estado = $estado;
        $this->fechaCreacion = $fechaCreacion;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getTelefono(): string { return $this->telefono; }
    public function getDireccion(): string { return $this->direccion; }
    public function getEstado(): bool { return $this->estado; }
    public function getFechaCreacion(): \DateTime { return $this->fechaCreacion; }

    // Setters (si necesitas mutabilidad después de la creación)
    public function setId(int $id): void { $this->id = $id; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelefono(string $telefono): void { $this->telefono = $telefono; }
    public function setDireccion(string $direccion): void { $this->direccion = $direccion; }
    public function setEstado(bool $estado): void { $this->estado = $estado; }
    public function setFechaCreacion(\DateTime $fechaCreacion): void { $this->fechaCreacion = $fechaCreacion; }
}
