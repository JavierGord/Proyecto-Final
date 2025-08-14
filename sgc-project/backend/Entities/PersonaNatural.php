<?php
declare(strict_types=1);

namespace App\Entities;

require_once 'Cliente.php'; 

class PersonaNatural extends Cliente {
    private string $nombres;
    private string $apellidos;
    private string $cedula;

    public function __construct(
        int $id,
        string $email,
        string $telefono,
        string $direccion,
        bool $estado,
        \DateTime $fechaCreacion,
        string $nombres,
        string $apellidos,
        string $cedula
    ) {
        parent::__construct($id, $email, $telefono, $direccion, $estado, $fechaCreacion);
        $this->nombres = $nombres;
        $this->apellidos = $apellidos;
        $this->cedula = $cedula;
    }

    // Getters
    public function getNombres(): string { return $this->nombres; }
    public function getApellidos(): string { return $this->apellidos; }
    public function getCedula(): string { return $this->cedula; }

    // Setters
    public function setNombres(string $nombres): void { $this->nombres = $nombres; }
    public function setApellidos(string $apellidos): void { $this->apellidos = $apellidos; }
    public function setCedula(string $cedula): void { $this->cedula = $cedula; }

    /**
     * Valida una cédula ecuatoriana.
     * Este es un ejemplo básico y puede requerir una implementación más robusta.
     * @return bool True si la cédula es válida, false en caso contrario.
     */
    public function validarCedula(): bool {
        // Implementación básica de validación de cédula ecuatoriana (módulo 10)
        if (strlen($this->cedula) !== 10 || !ctype_digit($this->cedula)) {
            return false;
        }

        $provincia = (int)substr($this->cedula, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return false;
        }

        $digitoVerificador = (int)substr($this->cedula, 9, 1);
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $valor = (int)$this->cedula[$i] * $coeficientes[$i];
            if ($valor >= 10) {
                $valor -= 9;
            }
            $suma += $valor;
        }

        $residuo = $suma % 10;
        $resultado = ($residuo === 0) ? 0 : (10 - $residuo);

        return $resultado === $digitoVerificador;
    }
}
