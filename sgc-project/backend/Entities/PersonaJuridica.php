<?php
declare(strict_types=1);

namespace App\Entities;

require_once 'Cliente.php'; 

class PersonaJuridica extends Cliente {
    private string $razonSocial;
    private string $ruc;
    private string $representanteLegal;

    public function __construct(
        int $id,
        string $email,
        string $telefono,
        string $direccion,
        bool $estado,
        \DateTime $fechaCreacion,
        string $razonSocial,
        string $ruc,
        string $representanteLegal
    ) {
        parent::__construct($id, $email, $telefono, $direccion, $estado, $fechaCreacion);
        $this->razonSocial = $razonSocial;
        $this->ruc = $ruc;
        $this->representanteLegal = $representanteLegal;
    }

    // Getters
    public function getRazonSocial(): string { return $this->razonSocial; }
    public function getRuc(): string { return $this->ruc; }
    public function getRepresentanteLegal(): string { return $this->representanteLegal; }

    // Setters
    public function setRazonSocial(string $razonSocial): void { $this->razonSocial = $razonSocial; }
    public function setRuc(string $ruc): void { $this->ruc = $ruc; }
    public function setRepresentanteLegal(string $representanteLegal): void { $this->representanteLegal = $representanteLegal; }

    /**
     * Valida un RUC ecuatoriano.
     * Este es un ejemplo básico y puede requerir una implementación más robusta.
     * @return bool True si el RUC es válido, false en caso contrario.
     */
    public function validarRuc(): bool {
        // Implementación básica de validación de RUC ecuatoriano
        // Un RUC puede ser de 10 o 13 dígitos.
        // Para empresas, suele ser de 13 dígitos y termina en 001.
        if (!ctype_digit($this->ruc)) {
            return false;
        }

        $length = strlen($this->ruc);
        if ($length !== 10 && $length !== 13) {
            return false;
        }

        // Validación más compleja para RUCs de 13 dígitos (empresas)
        if ($length === 13) {
            if (substr($this->ruc, 10, 3) !== '001') {
                return false; // RUC de empresa debe terminar en 001
            }
            // Se podría reutilizar la lógica de cédula para los primeros 10 dígitos
            $cedulaBase = substr($this->ruc, 0, 10);
            $tempPersonaNatural = new PersonaNatural(null, '', null, null, true, new \DateTime(), '', '', $cedulaBase);
            return $tempPersonaNatural->validarCedula();
        } elseif ($length === 10) {
            // Si es de 10 dígitos, se valida como cédula
            $tempPersonaNatural = new PersonaNatural(null, '', null, null, true, new \DateTime(), '', '', $this->ruc);
            return $tempPersonaNatural->validarCedula();
        }

        return false;
    }
}
