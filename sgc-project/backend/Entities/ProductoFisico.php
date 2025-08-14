<?php
declare(strict_types=1);

namespace App\Entities;

require_once 'Producto.php';

class ProductoFisico extends Producto {
    private float $peso;
    private float $alto;
    private float $ancho;
    private float $profundidad;

    public function __construct(
        int $id,
        string $nombre,
        string $descripcion,
        float $precioUnitario,
        int $stock,
        int $idCategoria,
        bool $estado,
        \DateTime $fechaCreacion,
        float $peso,
        float $alto,
        float $ancho,
        float $profundidad
    ) {
        parent::__construct($id, $nombre, $descripcion, $precioUnitario, $stock, $idCategoria, $estado, $fechaCreacion);
        $this->peso = $peso;
        $this->alto = $alto;
        $this->ancho = $ancho;
        $this->profundidad = $profundidad;
    }

    // Getters
    public function getPeso(): float { return $this->peso; }
    public function getAlto(): float { return $this->alto; }
    public function getAncho(): float { return $this->ancho; }
    public function getProfundidad(): float { return $this->profundidad; }

    // Setters
    public function setPeso(float $peso): void { $this->peso = $peso; }
    public function setAlto(float $alto): void { $this->alto = $alto; }
    public function setAncho(float $ancho): void { $this->ancho = $ancho; }
    public function setProfundidad(float $profundidad): void { $this->profundidad = $profundidad; }
}
