<?php
declare(strict_types=1);

namespace App\Entities;

class Usuario {
    private int $id;
    private string $username;
    private string $passwordHash; // Contraseña hasheada
    private bool $estado;
    private \DateTime $fechaCreacion;

    public function __construct(
        int $id,
        string $username,
        string $passwordHash,
        bool $estado,
        \DateTime $fechaCreacion
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->estado = $estado;
        $this->fechaCreacion = $fechaCreacion;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getEstado(): bool { return $this->estado; }
    public function getFechaCreacion(): \DateTime { return $this->fechaCreacion; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function setPasswordHash(string $passwordHash): void { $this->passwordHash = $passwordHash; }
    public function setEstado(bool $estado): void { $this->estado = $estado; }
    public function setFechaCreacion(\DateTime $fechaCreacion): void { $this->fechaCreacion = $fechaCreacion; }

    /**
     * Verifica si una contraseña plana coincide con el hash almacenado.
     * @param string $plainPassword La contraseña en texto plano.
     * @return bool True si coincide, false en caso contrario.
     */
    public function verifyPassword(string $plainPassword): bool {
        return password_verify($plainPassword, $this->passwordHash);
    }

    /**
     * Establece la contraseña hasheando el texto plano.
     * @param string $plainPassword La contraseña en texto plano.
     */
    public function setPassword(string $plainPassword): void {
        // Se recomienda PASSWORD_ARGON2ID para nuevas aplicaciones
        $this->passwordHash = password_hash($plainPassword, PASSWORD_ARGON2ID);
    }
}
