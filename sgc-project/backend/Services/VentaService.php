<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Entities\Venta;
use App\Entities\DetalleVenta;
use PDO;
use Exception;

class VentaService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Crea una nueva venta llamando al procedimiento almacenado `sp_crear_venta`.
     *
     * @param array $data Array asociativo con los datos de la venta:
     *                    - 'id_cliente': ID del cliente.
     *                    - 'id_usuario': ID del usuario que realiza la venta.
     *                    - 'detalles': Array de objetos JSON con {idProducto, cantidad, precioUnitario}.
     * @return array Un array con 'success' (bool), 'message' (string) y 'id_venta' (int) si es exitoso.
     */
    public function crearVenta(array $data): array {
        $idVenta = 0;
        $mensaje = '';

        try {
            // Validar existencia de cliente y usuario (opcional, pero recomendado)
            $stmtCliente = $this->db->prepare("SELECT id FROM clientes WHERE id = :id_cliente AND estado = TRUE");
            $stmtCliente->execute([':id_cliente' => $data['id_cliente']]);
            if (!$stmtCliente->fetch()) {
                throw new Exception("Cliente con ID " . $data['id_cliente'] . " no encontrado o inactivo.");
            }

            $stmtUsuario = $this->db->prepare("SELECT id FROM usuarios WHERE id = :id_usuario AND estado = TRUE");
            $stmtUsuario->execute([':id_usuario' => $data['id_usuario']]);
            if (!$stmtUsuario->fetch()) {
                throw new Exception("Usuario con ID " . $data['id_usuario'] . " no encontrado o inactivo.");
            }

            // Llamar al procedimiento almacenado sp_crear_venta
            // Nota: PDO::PARAM_STR es seguro para JSON, PDO lo manejará correctamente.
            $stmt = $this->db->prepare("CALL sp_crear_venta(:id_cliente, :id_usuario, :detalles, @p_id_venta, @p_mensaje)");
            $stmt->bindValue(':id_cliente', $data['id_cliente'], PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindValue(':detalles', json_encode($data['detalles']), PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor(); // Importante para poder obtener los resultados de las variables de salida

            // Obtener los valores de las variables de salida del procedimiento almacenado
            $result = $this->db->query("SELECT @p_id_venta AS id_venta, @p_mensaje AS mensaje")->fetch(PDO::FETCH_ASSOC);
            $idVenta = (int)$result['id_venta'];
            $mensaje = $result['mensaje'];

            if ($idVenta > 0) {
                return ['success' => true, 'message' => $mensaje, 'id_venta' => $idVenta];
            } else {
                // El procedimiento almacenado ya maneja los errores y devuelve un mensaje
                return ['success' => false, 'message' => $mensaje];
            }
        } catch (Exception $e) {
            // Captura cualquier excepción PHP antes de llamar al SP o si el SP no la maneja
            return ['success' => false, 'message' => "Error en el servicio de venta: " . $e->getMessage()];
        }
    }

    /**
     * Obtiene los detalles completos de una venta por su ID, incluyendo cliente, usuario y productos.
     *
     * @param int $id El ID de la venta.
     * @return array|null Un array asociativo con los datos de la venta y sus detalles, o null si no se encuentra.
     */
    public function obtenerVentaPorId(int $id): ?array {
        $sql = "
            SELECT
                v.id AS id_venta, v.fecha, v.total, v.estado,
                c.id AS cliente_id, c.tipo_cliente, c.email AS cliente_email, c.telefono AS cliente_telefono,
                c.nombres AS cliente_nombres, c.apellidos AS cliente_apellidos, c.cedula AS cliente_cedula,
                c.razon_social AS cliente_razon_social, c.ruc AS cliente_ruc,
                u.id AS usuario_id, u.username AS usuario_username
            FROM ventas v
            JOIN clientes c ON v.id_cliente = c.id
            JOIN usuarios u ON v.id_usuario = u.id
            WHERE v.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$venta) {
            return null;
        }

        $sqlDetalles = "
            SELECT
                dv.line_number, dv.cantidad, dv.precio_unitario, dv.subtotal,
                p.id AS producto_id, p.nombre AS producto_nombre, p.descripcion AS producto_descripcion,
                p.tipo_producto, p.peso, p.alto, p.ancho, p.profundidad, p.url_descarga, p.licencia
            FROM detalle_venta dv
            JOIN productos p ON dv.id_producto = p.id
            WHERE dv.id_venta = :id_venta
            ORDER BY dv.line_number
        ";
        $stmtDetalles = $this->db->prepare($sqlDetalles);
        $stmtDetalles->execute([':id_venta' => $id]);
        $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

        $venta['detalles'] = $detalles;
        return $venta;
    }

    /**
     * Lista las últimas N ventas.
     *
     * @param int $limit El número máximo de ventas a retornar.
     * @return array Un array de ventas.
     */
    public function listarUltimasVentas(int $limit = 20): array {
        $sql = "
            SELECT
                v.id AS id_venta, v.fecha, v.total, v.estado,
                c.nombres AS cliente_nombres, c.apellidos AS cliente_apellidos, c.razon_social AS cliente_razon_social,
                u.username AS usuario_username
            FROM ventas v
            JOIN clientes c ON v.id_cliente = c.id
            JOIN usuarios u ON v.id_usuario = u.id
            ORDER BY v.fecha DESC
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de una venta.
     *
     * @param int $id El ID de la venta.
     * @param string $nuevoEstado El nuevo estado de la venta ('EMITIDA', 'ANULADA', etc.).
     * @return array Un array con 'success' (bool) y 'message' (string).
     */
    public function actualizarEstadoVenta(int $id, string $nuevoEstado): array {
        $this->db->beginTransaction();
        try {
            // Validar que el nuevo estado sea uno permitido
            $estadosPermitidos = ['BORRADOR', 'EMITIDA', 'ANULADA'];
            if (!in_array($nuevoEstado, $estadosPermitidos)) {
                throw new Exception("Estado de venta no válido: {$nuevoEstado}.");
            }

            $stmt = $this->db->prepare("UPDATE ventas SET estado = :estado WHERE id = :id");
            $stmt->execute([
                ':estado' => $nuevoEstado,
                ':id' => $id
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Venta con ID {$id} no encontrada o estado ya es {$nuevoEstado}.");
            }

            $this->db->commit();
            return ['success' => true, 'message' => "Estado de venta actualizado a {$nuevoEstado}."];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "Error al actualizar estado de venta: " . $e->getMessage()];
        }
    }
}
