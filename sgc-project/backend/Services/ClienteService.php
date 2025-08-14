<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Entities\PersonaNatural;
use App\Entities\PersonaJuridica;
use PDO;
use Exception;

class ClienteService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Crea un nuevo cliente (Persona Natural o Jurídica) en la base de datos.
     *
     * @param array $data Array asociativo con los datos del cliente.
     *                    Debe contener 'tipo_cliente' ('NATURAL' o 'JURIDICA')
     *                    y los campos específicos de cada tipo.
     * @return array Un array con 'success' (bool), 'message' (string) y 'id' (int) si es exitoso.
     */
    public function crearCliente(array $data): array {
        $this->db->beginTransaction();
        try {
            if (!isset($data['tipo_cliente'])) {
                throw new Exception("El tipo de cliente es requerido.");
            }

            $cliente = null;
            $stmt = null;

            // Campos comunes a ambos tipos de cliente
            $email = $data['email'] ?? '';
            $telefono = $data['telefono'] ?? null;
            $direccion = $data['direccion'] ?? null;
            $estado = $data['estado'] ?? true; // Por defecto activo
            $fechaCreacion = new \DateTime($data['fecha_creacion'] ?? 'now');

            if ($data['tipo_cliente'] === 'NATURAL') {
                $cliente = new PersonaNatural(
                    null, // ID será asignado por la BD
                    $email,
                    $telefono,
                    $direccion,
                    $estado,
                    $fechaCreacion,
                    $data['nombres'] ?? '',
                    $data['apellidos'] ?? '',
                    $data['cedula'] ?? ''
                );

                if (!$cliente->validarCedula()) {
                    throw new Exception("Cédula no válida.");
                }

                $stmt = $this->db->prepare("
                    INSERT INTO clientes (tipo_cliente, email, telefono, direccion, nombres, apellidos, cedula, estado, fecha_creacion)
                    VALUES (:tipo_cliente, :email, :telefono, :direccion, :nombres, :apellidos, :cedula, :estado, :fecha_creacion)
                ");
                $stmt->execute([
                    ':tipo_cliente' => 'NATURAL',
                    ':email' => $cliente->getEmail(),
                    ':telefono' => $cliente->getTelefono(),
                    ':direccion' => $cliente->getDireccion(),
                    ':nombres' => $cliente->getNombres(),
                    ':apellidos' => $cliente->getApellidos(),
                    ':cedula' => $cliente->getCedula(),
                    ':estado' => $cliente->getEstado(),
                    ':fecha_creacion' => $cliente->getFechaCreacion()->format('Y-m-d H:i:s')
                ]);
            } elseif ($data['tipo_cliente'] === 'JURIDICA') {
                $cliente = new PersonaJuridica(
                    null, // ID será asignado por la BD
                    $email,
                    $telefono,
                    $direccion,
                    $estado,
                    $fechaCreacion,
                    $data['razon_social'] ?? '',
                    $data['ruc'] ?? '',
                    $data['representante_legal'] ?? ''
                );

                if (!$cliente->validarRuc()) {
                    throw new Exception("RUC no válido.");
                }

                $stmt = $this->db->prepare("
                    INSERT INTO clientes (tipo_cliente, email, telefono, direccion, razon_social, ruc, representante_legal, estado, fecha_creacion)
                    VALUES (:tipo_cliente, :email, :telefono, :direccion, :razon_social, :ruc, :representante_legal, :estado, :fecha_creacion)
                ");
                $stmt->execute([
                    ':tipo_cliente' => 'JURIDICA',
                    ':email' => $cliente->getEmail(),
                    ':telefono' => $cliente->getTelefono(),
                    ':direccion' => $cliente->getDireccion(),
                    ':razon_social' => $cliente->getRazonSocial(),
                    ':ruc' => $cliente->getRuc(),
                    ':representante_legal' => $cliente->getRepresentanteLegal(),
                    ':estado' => $cliente->getEstado(),
                    ':fecha_creacion' => $cliente->getFechaCreacion()->format('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception("Tipo de cliente no válido: " . $data['tipo_cliente']);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Cliente creado exitosamente', 'id' => (int)$this->db->lastInsertId()];
        } catch (Exception $e) {
            $this->db->rollBack();
            // En producción, loguear $e->getMessage() y devolver un mensaje genérico.
            return ['success' => false, 'message' => "Error al crear cliente: " . $e->getMessage()];
        }
    }

    /**
     * Busca clientes por una cadena de consulta en nombres, apellidos, razón social, cédula o RUC.
     *
     * @param string $query La cadena de búsqueda.
     * @return array Un array de clientes que coinciden con la consulta.
     */
    public function buscarClientes(string $query = ''): array {
        $sql = "
            SELECT 
                id, tipo_cliente, email, telefono, direccion, 
                nombres, apellidos, cedula, 
                razon_social, ruc, representante_legal, 
                estado, fecha_creacion
            FROM clientes 
            WHERE nombres LIKE :query 
               OR apellidos LIKE :query 
               OR razon_social LIKE :query 
               OR cedula LIKE :query 
               OR ruc LIKE :query 
            LIMIT 20 -- Limita los resultados para evitar sobrecarga
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un cliente por su ID.
     *
     * @param int $id El ID del cliente.
     * @return array|null Un array asociativo con los datos del cliente o null si no se encuentra.
     */
    public function obtenerClientePorId(int $id): ?array {
        $sql = "
            SELECT 
                id, tipo_cliente, email, telefono, direccion, 
                nombres, apellidos, cedula, 
                razon_social, ruc, representante_legal, 
                estado, fecha_creacion
            FROM clientes 
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Aquí podrías hidratar a un objeto Cliente (PersonaNatural o PersonaJuridica)
        // para devolver un objeto tipado en lugar de un array asociativo, si fuera necesario.
        // Por simplicidad para la API, devolvemos el array.
        return $row;
    }

    /**
     * Actualiza los datos de un cliente existente.
     *
     * @param int $id El ID del cliente a actualizar.
     * @param array $data Los nuevos datos del cliente.
     * @return array Un array con 'success' (bool) y 'message' (string).
     */
    public function actualizarCliente(int $id, array $data): array {
        $this->db->beginTransaction();
        try {
            $clienteExistente = $this->obtenerClientePorId($id);
            if (!$clienteExistente) {
                throw new Exception("Cliente con ID {$id} no encontrado.");
            }

            $tipoCliente = $clienteExistente['tipo_cliente'];
            $sql = "UPDATE clientes SET ";
            $params = [':id' => $id];
            $updates = [];

            // Campos comunes
            if (isset($data['email'])) { $updates[] = "email = :email"; $params[':email'] = $data['email']; }
            if (isset($data['telefono'])) { $updates[] = "telefono = :telefono"; $params[':telefono'] = $data['telefono']; }
            if (isset($data['direccion'])) { $updates[] = "direccion = :direccion"; $params[':direccion'] = $data['direccion']; }
            if (isset($data['estado'])) { $updates[] = "estado = :estado"; $params[':estado'] = $data['estado']; }

            if ($tipoCliente === 'NATURAL') {
                if (isset($data['nombres'])) { $updates[] = "nombres = :nombres"; $params[':nombres'] = $data['nombres']; }
                if (isset($data['apellidos'])) { $updates[] = "apellidos = :apellidos"; $params[':apellidos'] = $data['apellidos']; }
                if (isset($data['cedula'])) {
                    $updates[] = "cedula = :cedula";
                    $params[':cedula'] = $data['cedula'];
                    // Validar cédula si se actualiza
                    $tempCliente = new PersonaNatural($id, '', null, null, true, new \DateTime(), $data['nombres'] ?? '', $data['apellidos'] ?? '', $data['cedula']);
                    if (!$tempCliente->validarCedula()) {
                        throw new Exception("Cédula actualizada no válida.");
                    }
                }
            } elseif ($tipoCliente === 'JURIDICA') {
                if (isset($data['razon_social'])) { $updates[] = "razon_social = :razon_social"; $params[':razon_social'] = $data['razon_social']; }
                if (isset($data['ruc'])) {
                    $updates[] = "ruc = :ruc";
                    $params[':ruc'] = $data['ruc'];
                    // Validar RUC si se actualiza
                    $tempCliente = new PersonaJuridica($id, '', null, null, true, new \DateTime(), $data['razon_social'] ?? '', $data['ruc'], $data['representante_legal'] ?? '');
                    if (!$tempCliente->validarRuc()) {
                        throw new Exception("RUC actualizado no válido.");
                    }
                }
                if (isset($data['representante_legal'])) { $updates[] = "representante_legal = :representante_legal"; $params[':representante_legal'] = $data['representante_legal']; }
            }

            if (empty($updates)) {
                throw new Exception("No hay datos para actualizar.");
            }

            $sql .= implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->db->commit();
            return ['success' => true, 'message' => 'Cliente actualizado exitosamente.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "Error al actualizar cliente: " . $e->getMessage()];
        }
    }

    /**
     * Elimina un cliente por su ID.
     *
     * @param int $id El ID del cliente a eliminar.
     * @return array Un array con 'success' (bool) y 'message' (string).
     */
    public function eliminarCliente(int $id): array {
        $this->db->beginTransaction();
        try {
            // Considerar eliminación lógica (cambiar estado a inactivo) en lugar de física
            $stmt = $this->db->prepare("UPDATE clientes SET estado = FALSE WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Cliente con ID {$id} no encontrado o ya inactivo.");
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Cliente eliminado (inactivado) exitosamente.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "Error al eliminar cliente: " . $e->getMessage()];
        }
    }
}
