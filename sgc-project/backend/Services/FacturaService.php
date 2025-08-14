<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Entities\Factura;
use PDO;
use Exception;

class FacturaService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Emite una factura para una venta específica.
     *
     * @param int $idVenta El ID de la venta para la cual se emitirá la factura.
     * @return array Un array con 'success' (bool), 'message' (string), 'id_factura' (int) y 'numero_factura' (string).
     */
    public function emitirFactura(int $idVenta): array {
        $this->db->beginTransaction();
        try {
            // 1. Verificar si la venta existe y si ya tiene una factura asociada
            $stmtVenta = $this->db->prepare("SELECT id, estado FROM ventas WHERE id = :id_venta");
            $stmtVenta->execute([':id_venta' => $idVenta]);
            $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                throw new Exception("Venta con ID {$idVenta} no encontrada.");
            }
            if ($venta['estado'] !== 'EMITIDA') {
                throw new Exception("La venta no está en estado 'EMITIDA' para facturar. Estado actual: " . $venta['estado']);
            }

            // Verificar si ya existe una factura para esta venta
            $stmtFacturaExistente = $this->db->prepare("SELECT id FROM facturas WHERE id_venta = :id_venta");
            $stmtFacturaExistente->execute([':id_venta' => $idVenta]);
            if ($stmtFacturaExistente->fetch()) {
                throw new Exception("Ya existe una factura para la venta con ID {$idVenta}.");
            }

            // 2. Generar número de factura (ejemplo simple, en producción sería más complejo y único)
            // Podría ser un correlativo, o generado por un sistema externo.
            $numeroFactura = 'FAC-' . date('Ymd') . '-' . str_pad((string)$idVenta, 6, '0', STR_PAD_LEFT);
            // Asegurar unicidad del número de factura (aunque la BD ya tiene UNIQUE)
            $counter = 0;
            $tempNumeroFactura = $numeroFactura;
            while (true) {
                $stmtCheckNum = $this->db->prepare("SELECT id FROM facturas WHERE numero = :numero");
                $stmtCheckNum->execute([':numero' => $tempNumeroFactura]);
                if (!$stmtCheckNum->fetch()) {
                    $numeroFactura = $tempNumeroFactura;
                    break;
                }
                $counter++;
                $tempNumeroFactura = $numeroFactura . '-' . $counter;
                if ($counter > 100) { // Evitar bucles infinitos
                    throw new Exception("No se pudo generar un número de factura único.");
                }
            }


            // 3. Generar clave de acceso (ejemplo, para sistemas de facturación electrónica)
            $claveAcceso = hash('sha256', $numeroFactura . microtime() . rand(1000, 9999)); // Más aleatorio

            // 4. Insertar la factura
            $stmt = $this->db->prepare("
                INSERT INTO facturas (id_venta, numero, clave_acceso, fecha_emision, estado)
                VALUES (:id_venta, :numero, :clave_acceso, :fecha_emision, :estado)
            ");
            $stmt->execute([
                ':id_venta' => $idVenta,
                ':numero' => $numeroFactura,
                ':clave_acceso' => $claveAcceso,
                ':fecha_emision' => (new \DateTime())->format('Y-m-d H:i:s'),
                ':estado' => 'PENDIENTE' // Estado inicial, luego podría ser 'AUTORIZADA' por un proceso externo
            ]);

            $idFactura = (int)$this->db->lastInsertId();

            // 5. Opcional: Actualizar estado de la venta a 'FACTURADA' si tu modelo lo requiere
            // $ventaService = new VentaService();
            // $ventaService->actualizarEstadoVenta($idVenta, 'FACTURADA');

            $this->db->commit();
            return ['success' => true, 'message' => 'Factura emitida exitosamente', 'id_factura' => $idFactura, 'numero_factura' => $numeroFactura];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "Error al emitir factura: " . $e->getMessage()];
        }
    }

    /**
     * Obtiene una factura por su ID.
     *
     * @param int $id El ID de la factura.
     * @return array|null Un array asociativo con los datos de la factura o null si no se encuentra.
     */
    public function obtenerFacturaPorId(int $id): ?array {
        $sql = "
            SELECT 
                f.id, f.id_venta, f.numero, f.clave_acceso, f.fecha_emision, f.estado,
                v.total AS venta_total, v.fecha AS venta_fecha,
                c.nombres AS cliente_nombres, c.apellidos AS cliente_apellidos, c.razon_social AS cliente_razon_social
            FROM facturas f
            JOIN ventas v ON f.id_venta = v.id
            JOIN clientes c ON v.id_cliente = c.id
            WHERE f.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una factura por el ID de la venta asociada.
     *
     * @param int $idVenta El ID de la venta.
     * @return array|null Un array asociativo con los datos de la factura o null si no se encuentra.
     */
    public function obtenerFacturaPorIdVenta(int $idVenta): ?array {
        $sql = "
            SELECT 
                f.id, f.id_venta, f.numero, f.clave_acceso, f.fecha_emision, f.estado,
                v.total AS venta_total, v.fecha AS venta_fecha,
                c.nombres AS cliente_nombres, c.apellidos AS cliente_apellidos, c.razon_social AS cliente_razon_social
            FROM facturas f
            JOIN ventas v ON f.id_venta = v.id
            JOIN clientes c ON v.id_cliente = c.id
            WHERE f.id_venta = :id_venta
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_venta' => $idVenta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas las facturas.
     *
     * @param int $limit Límite de resultados.
     * @return array Un array de facturas.
     */
    public function listarFacturas(int $limit = 20): array {
        $sql = "
            SELECT
                f.id, f.id_venta, f.numero, f.fecha_emision, f.estado,
                v.total AS venta_total,
                c.nombres AS cliente_nombres, c.apellidos AS cliente_apellidos, c.razon_social AS cliente_razon_social
            FROM facturas f
            JOIN ventas v ON f.id_venta = v.id
            JOIN clientes c ON v.id_cliente = c.id
            ORDER BY f.fecha_emision DESC
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
