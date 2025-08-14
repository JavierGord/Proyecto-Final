<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Entities\ProductoFisico;
use App\Entities\ProductoDigital;
use App\Entities\Categoria; // Necesario si se usa para validar o hidratar
use PDO;
use Exception;

class ProductoService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Crea un nuevo producto (Físico o Digital) en la base de datos.
     *
     * @param array $data Array asociativo con los datos del producto.
     *                    Debe contener 'tipo_producto' ('FISICO' o 'DIGITAL')
     *                    y los campos específicos de cada tipo.
     * @return array Un array con 'success' (bool), 'message' (string) y 'id' (int) si es exitoso.
     */
    public function crearProducto(array $data): array {
        $this->db->beginTransaction();
        try {
            if (!isset($data['tipo_producto'])) {
                throw new Exception("El tipo de producto es requerido.");
            }
            if (!isset($data['nombre'], $data['precio_unitario'], $data['stock'], $data['id_categoria'])) {
                throw new Exception("Campos básicos de producto (nombre, precio, stock, categoría) son requeridos.");
            }

            // Validar que la categoría exista
            $stmtCat = $this->db->prepare("SELECT id FROM categorias WHERE id = :id_categoria AND estado = TRUE");
            $stmtCat->execute([':id_categoria' => (int)$data['id_categoria']]);
            if (!$stmtCat->fetch()) {
                throw new Exception("Categoría con ID " . $data['id_categoria'] . " no encontrada o inactiva.");
            }

            $producto = null;
            $stmt = null;

            // Campos comunes a ambos tipos de producto
            $nombre = $data['nombre'];
            $descripcion = $data['descripcion'] ?? null;
            $precioUnitario = (float)$data['precio_unitario'];
            $stock = (int)$data['stock'];
            $idCategoria = (int)$data['id_categoria'];
            $estado = $data['estado'] ?? true; // Por defecto activo
            $fechaCreacion = new \DateTime($data['fecha_creacion'] ?? 'now');

            if ($data['tipo_producto'] === 'FISICO') {
                $producto = new ProductoFisico(
                    null, // ID será asignado por la BD
                    $nombre,
                    $descripcion,
                    $precioUnitario,
                    $stock,
                    $idCategoria,
                    $estado,
                    $fechaCreacion,
                    (float)($data['peso'] ?? 0.0),
                    (float)($data['alto'] ?? 0.0),
                    (float)($data['ancho'] ?? 0.0),
                    (float)($data['profundidad'] ?? 0.0)
                );

                $stmt = $this->db->prepare("
                    INSERT INTO productos (tipo_producto, nombre, descripcion, precio_unitario, stock, id_categoria, peso, alto, ancho, profundidad, estado, fecha_creacion)
                    VALUES (:tipo_producto, :nombre, :descripcion, :precio_unitario, :stock, :id_categoria, :peso, :alto, :ancho, :profundidad, :estado, :fecha_creacion)
                ");
                $stmt->execute([
                    ':tipo_producto' => 'FISICO',
                    ':nombre' => $producto->getNombre(),
                    ':descripcion' => $producto->getDescripcion(),
                    ':precio_unitario' => $producto->getPrecioUnitario(),
                    ':stock' => $producto->getStock(),
                    ':id_categoria' => $producto->getIdCategoria(),
                    ':peso' => $producto->getPeso(),
                    ':alto' => $producto->getAlto(),
                    ':ancho' => $producto->getAncho(),
                    ':profundidad' => $producto->getProfundidad(),
                    ':estado' => $producto->getEstado(),
                    ':fecha_creacion' => $producto->getFechaCreacion()->format('Y-m-d H:i:s')
                ]);
            } elseif ($data['tipo_producto'] === 'DIGITAL') {
                $producto = new ProductoDigital(
                    null, // ID será asignado por la BD
                    $nombre,
                    $descripcion,
                    $precioUnitario,
                    $stock,
                    $idCategoria,
                    $estado,
                    $fechaCreacion,
                    $data['url_descarga'] ?? null,
                    $data['licencia'] ?? null
                );

                $stmt = $this->db->prepare("
                    INSERT INTO productos (tipo_producto, nombre, descripcion, precio_unitario, stock, id_categoria, url_descarga, licencia, estado, fecha_creacion)
                    VALUES (:tipo_producto, :nombre, :descripcion, :precio_unitario, :stock, :id_categoria, :url_descarga, :licencia, :estado, :fecha_creacion)
                ");
                $stmt->execute([
                    ':tipo_producto' => 'DIGITAL',
                    ':nombre' => $producto->getNombre(),
                    ':descripcion' => $producto->getDescripcion(),
                    ':precio_unitario' => $producto->getPrecioUnitario(),
                    ':stock' => $producto->getStock(),
                    ':id_categoria' => $producto->getIdCategoria(),
                    ':url_descarga' => $producto->getUrlDescarga(),
                    ':licencia' => $producto->getLicencia(),
                    ':estado' => $producto->getEstado(),
                    ':fecha_creacion' => $producto->getFechaCreacion()->format('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception("Tipo de producto no válido: " . $data['tipo_producto']);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Producto creado exitosamente', 'id' => (int)$this->db->lastInsertId()];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "Error al crear producto: " . $e->getMessage()];
        }
    }

    /**
     * Lista todos los productos activos.
     *
     * @return array Un array de productos.
     */
    public function listarProductos(): array {
        $sql = "
            SELECT 
                p.id, p.tipo_producto, p.nombre, p.descripcion, p.precio_unitario, p.stock, p.estado, p.fecha_creacion,
                p.peso, p.alto, p.ancho, p.profundidad,
                p.url_descarga, p.licencia,
                c.nombre AS categoria_nombre
            FROM productos p
            JOIN categorias c ON p.id_categoria = c.id
            WHERE p.estado = TRUE
            ORDER BY p.nombre ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un producto por su ID.
     *
     * @param int $id El ID del producto.
     * @return array|null Un array asociativo con los datos del producto o null si no se encuentra.
     */
    public function obtenerProductoPorId(int $id): ?array {
        $sql = "
            SELECT 
                p.id, p.tipo_producto, p.nombre, p.descripcion, p.precio_unitario, p.stock, p.estado, p.fecha_creacion,
                p.peso, p.alto, p.ancho, p.profundidad,
                p.url_descarga, p.licencia,
                c.nombre AS categoria_nombre
            FROM productos p
            JOIN categorias c ON p.id_categoria = c.id
            WHERE p.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza los datos de un producto existente.
     *
     * @param int $id El ID del producto a actualizar.
     * @param array $data Los nuevos datos del producto.
     * @return array Un array con 'success' (bool) y 'message' (string).
     */
    public function actualizarProducto(int $id, array $data): array {
        $this->db->beginTransaction();
        try {
            $productoExistente = $this->obtenerProductoPorId($id);
            if (!$productoExistente) {
                throw new Exception("Producto con ID {$id} no encontrado.");
            }

            $tipoProducto = $productoExistente['tipo_producto'];
            $sql = "UPDATE productos SET ";
            $params = [':id' => $id];
            $updates = [];

            // Campos comunes
            if (isset($data['nombre'])) { $updates[] = "nombre = :nombre"; $params[':nombre'] = $data['nombre']; }
            if (isset($data['descripcion'])) { $updates[] = "descripcion = :descripcion"; $params[':descripcion'] = $data['descripcion']; }
            if (isset($data['precio_unitario'])) { $updates[] = "precio_unitario = :precio_unitario"; $params[':precio_unitario'] = (float)$data['precio_unitario']; }
            if (isset($data['stock'])) { $updates[] = "stock = :stock"; $params[':stock'] = (int)$data['stock']; }
            if (isset($data['id_categoria'])) {
                // Validar que la nueva categoría exista
                $stmtCat = $this->db->prepare("SELECT id FROM categorias WHERE id = :id_categoria AND estado = TRUE");
                $stmtCat->execute([':id_categoria' => (int)$data['id_categoria']]);
                if (!$stmtCat->fetch()) {
                    throw new Exception("Categoría con ID " . $data['id_categoria'] . " no encontrada o inactiva.");
                }
                $updates[] = "id_categoria = :id_categoria"; $params[':id_categoria'] = (int)$data['id_categoria'];
            }
            if (isset($data['estado'])) { $updates[] = "estado = :estado"; $params[':estado'] = $data['estado']; }

            if ($tipoProducto === 'FISICO') {
                if (isset($data['peso'])) { $updates[] = "peso = :peso"; $params[':peso'] = (float)$data['peso']; }
                if (isset($data['alto'])) { $updates[] = "alto = :alto"; $params[':alto'] = (float)$data['alto']; }
                if (isset($data['ancho'])) { $updates[] = "ancho = :ancho"; $params[':ancho'] = (float)$data['ancho']; }
                if (isset($data['profundidad'])) { $updates[] = "profundidad = :profundidad"; $params[':profundidad'] = (float)$data['profundidad']; }
            } elseif ($tipoProducto === 'DIGITAL') {
                if (isset($data['url_descarga'])) { $updates[] = "url_descarga = :url_descarga"; $params[':url_descarga'] = $data['url_descarga']; }
                if (isset($data['licencia'])) { $updates[] = "licencia = :licencia"; $params[':licencia'] = $data['licencia']; }
            }

            if (empty($updates)) {
                throw new Exception("No hay datos para actualizar.");
            }

            $sql .= implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->db->commit();
            return ['success' => true, 'message' => 'Producto actualizado exitosamente.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "Error al actualizar producto: " . $e->getMessage()];
        }
    }

    /**
     * Elimina un producto por su ID (eliminación lógica).
     *
     * @param int $id El ID del producto a eliminar.
     * @return array Un array con 'success' (bool) y 'message' (string).
     */
    public function eliminarProducto(int $id): array {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE productos SET estado = FALSE WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Producto con ID {$id} no encontrado o ya inactivo.");
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Producto eliminado (inactivado) exitosamente.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "Error al eliminar producto: " . $e->getMessage()];
        }
    }
}
