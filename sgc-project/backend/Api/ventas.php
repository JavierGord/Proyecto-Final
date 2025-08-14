<?php
declare(strict_types=1);

namespace App\Api;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/VentaService.php';
require_once __DIR__ . '/../entities/Venta.php';
require_once __DIR__ . '/../entities/DetalleVenta.php';
// Incluir otras entidades si el servicio de venta las usa directamente para hidratación
require_once __DIR__ . '/../entities/Cliente.php';
require_once __DIR__ . '/../entities/Usuario.php';
require_once __DIR__ . '/../entities/Producto.php';

use App\Services\VentaService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$method = $_SERVER['REQUEST_METHOD'];
$ventaService = new VentaService();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch ($method) {
    case 'GET':
        $pathSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $id = null;
        // Asumiendo URL como /backend/ventas/{id}
        if (count($pathSegments) >= 3 && $pathSegments[count($pathSegments) - 2] === 'ventas' && is_numeric(end($pathSegments))) {
            $id = (int)end($pathSegments);
        }

        if ($id !== null) {
            $venta = $ventaService->obtenerVentaPorId($id);
            if ($venta) {
                echo json_encode(['success' => true, 'data' => $venta]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Venta no encontrada.']);
            }
        } else {
            // Listar últimas ventas si no se especifica ID
            $ventas = $ventaService->listarUltimasVentas();
            echo json_encode(['success' => true, 'data' => $ventas]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'JSON inválido en el cuerpo de la solicitud.']);
            break;
        }
        // Validar datos mínimos
        if (!isset($data['id_cliente'], $data['id_usuario'], $data['detalles']) || !is_array($data['detalles']) || empty($data['detalles'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos de venta incompletos o inválidos.']);
            break;
        }

        $result = $ventaService->crearVenta($data);
        if ($result['success']) {
            http_response_code(201);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        break;

    case 'PUT':
        // Para actualizar estado de venta: PUT /backend/ventas/{id}/estado
        $pathSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $id = null;
        $action = null;

        if (count($pathSegments) >= 4 && $pathSegments[count($pathSegments) - 3] === 'ventas' && is_numeric($pathSegments[count($pathSegments) - 2])) {
            $id = (int)$pathSegments[count($pathSegments) - 2];
            $action = end($pathSegments); // 'estado'
        }

        if ($id !== null && $action === 'estado') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['estado'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'JSON inválido o estado faltante.']);
                break;
            }
            $result = $ventaService->actualizarEstadoVenta($id, $data['estado']);
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } else {
            http_response_code(501); // Not Implemented for general PUT
            echo json_encode(['success' => false, 'message' => 'Método PUT no implementado o URL inválida para ventas.']);
        }
        break;

    case 'DELETE':
        http_response_code(501); // Not Implemented
        echo json_encode(['success' => false, 'message' => 'Método DELETE no implementado para ventas.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método HTTP no permitido para este recurso.']);
        break;
}
