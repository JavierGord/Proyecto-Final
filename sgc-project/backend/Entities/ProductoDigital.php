<?php
declare(strict_types=1);

namespace App\Entities;

require_once 'Producto.php';

class ProductoDigital extends Producto {
    private string $urlDescarga;
    private string $licencia;

    public function __construct(
        int $id,
        string $nombre,
        string $descripcion,
        float $precioUnitario,
        int $stock,
        int $idCategoria,
        bool $estado,
        \DateTime $fechaCreacion,
        string $urlDescarga,
        string $licencia
    ) {
        parent::__construct($id, $nombre, $descripcion, $precioUnitario, $stock, $idCategoria, $estado, $fechaCreacion);
        $this->urlDescarga = $urlDescarga;
        $this->licencia = $licencia;
    }

    // Getters
    public function getUrlDescarga(): string { return $this->urlDescarga; }
    public function getLicencia(): string { return $this->licencia; }

    // Setters
    public function setUrlDescarga(string $urlDescarga): void { $this->urlDescarga = $urlDescarga; }
    public function setLicencia(string $licencia): void { $this->licencia = $licencia; }
}
