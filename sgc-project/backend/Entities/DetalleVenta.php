<?php
declare(strict_types=1);

namespace App\Entities;

class DetalleVenta {
    private int $idVenta; // ID de la venta a la que pertenece este detalle
    private int $lineNumber; // Número de línea dentro de la venta (para orden)
    private int $idProducto; // ID del producto vendido
    private int $cantidad;
    private float $precioUnitario; // Precio del producto en el momento de la venta
    private float $subtotal; // Cantidad * PrecioUnitario

    public function __construct(
        int $idVenta,
        int $lineNumber,
        int $idProducto,
        int $cantidad,
        float $precioUnitario,
        float $subtotal
    ) {
        $this->idVenta = $idVenta;
        $this->lineNumber = $lineNumber;
        $this->idProducto = $idProducto;
        $this->cantidad = $cantidad;
        $this->precioUnitario = $precioUnitario;
        $this->subtotal = $subtotal;
    }

    // Getters
    public function getIdVenta(): int { return $this->idVenta; }
    public function getLineNumber(): int { return $this->lineNumber; }
    public function getIdProducto(): int { return $this->idProducto; }
    public function getCantidad(): int { return $this->cantidad; }
    public function getPrecioUnitario(): float { return $this->precioUnitario; }
    public function getSubtotal(): float { return $this->subtotal; }

    // Setters
    public function setIdVenta(int $idVenta): void { $this->idVenta = $idVenta; }
    public function setLineNumber(int $lineNumber): void { $this->lineNumber = $lineNumber; }
    public function setIdProducto(int $idProducto): void { $this->idProducto = $idProducto; }
    public function setCantidad(int $cantidad): void { $this->cantidad = $cantidad; }
    public function setPrecioUnitario(float $precioUnitario): void { $this->precioUnitario = $precioUnitario; }
    public function setSubtotal(float $subtotal): void { $this->subtotal = $subtotal; }
}
