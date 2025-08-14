<?php
declare(strict_types=1);

namespace App\Entities;

class Venta {
    private int $id;
    private \DateTime $fecha;
    private int $idCliente; // ID del cliente que realizó la venta
    private float $total;
    private string $estado; // Ej: 'BORRADOR', 'EMITIDA', 'ANULADA'
    private int $idUsuario; // ID del usuario que registró la venta

    public function __construct(
        int $id,
        \DateTime $fecha,
        int $idCliente,
        float $total,
        string $estado,
        int $idUsuario
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->idCliente = $idCliente;
        $this->total = $total;
        $this->estado = $estado;
        $this->idUsuario = $idUsuario;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getFecha(): \DateTime { return $this->fecha; }
    public function getIdCliente(): int { return $this->idCliente; }
    public function getTotal(): float { return $this->total; }
    public function getEstado(): string { return $this->estado; }
    public function getIdUsuario(): int { return $this->idUsuario; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setFecha(\DateTime $fecha): void { $this->fecha = $fecha; }
    public function setIdCliente(int $idCliente): void { $this->idCliente = $idCliente; }
    public function setTotal(float $total): void { $this->total = $total; }
    public function setEstado(string $estado): void { $this->estado = $estado; }
    public function setIdUsuario(int $idUsuario): void { $this->idUsuario = $idUsuario; }
}
