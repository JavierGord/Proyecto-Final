<?php
declare(strict_types=1);

namespace App\Api;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/FacturaService.php';
require_once __DIR__ . '/../services/VentaService.php'; // Necesario para verificar la venta
require_once __DIR__ . '/../entities/Factura.php';
require_once __DIR__ . '/../entities/Venta.php'; // Necesario para el servicio de venta

use App\Services\FacturaService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$method = $_SERVER['REQUEST_METHOD'];
$facturaService = new FacturaService();

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener el ID de la URL si existe (para GET /api/facturas/{id} o POST /api/facturas/{idVenta})
$pathSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$idFromUrl = null;
// La URL esperada es /backend/facturas/{id} o /backend/facturas/venta/{idVenta}
// O para POST, /backend/facturas/{idVenta} para emitir
if (count($pathSegments) >= 3 && $pathSegments[count($pathSegments) - 2] === 'facturas' && is_numeric(end($pathSegments))) {
    $idFromUrl = (int)end($pathSegments);
} elseif (count($pathSegments) >= 4 && $pathSegments[count($pathSegments) - 3] === 'facturas' && $pathSegments[count($pathSegments) - 2] === 'venta' && is_numeric(end($pathSegments))) {
    // Caso específico para buscar factura por ID de venta: /backend/facturas/venta/{idVenta}
    $idVentaFromUrl = (int)end($pathSegments);
}


switch ($method) {
    case 'GET':
        if (isset($idVentaFromUrl)) {
            $factura = $facturaService->obtenerFacturaPorIdVenta($idVentaFromUrl);
            if ($factura) {
                echo json_encode(['success' => true, 'data' => $factura]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Factura no encontrada para la venta especificada.']);
            }
        } elseif ($idFromUrl !== null) {
            $factura = $facturaService->obtenerFacturaPorId($idFromUrl);
            if ($factura) {
                echo json_encode(['success' => true, 'data' => $factura]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Factura no encontrada.']);
            }
        } else {
            // Listar todas las facturas (o las últimas N)
            $facturas = $facturaService->listarFacturas();
            echo json_encode(['success' => true, 'data' => $facturas]);
        }
        break;

    case 'POST':
        // Para emitir factura, se espera el ID de la venta en la URL: POST /backend/facturas/{idVenta}
        if ($idFromUrl !== null) {
            $idVenta = $idFromUrl;
            $result = $facturaService->emitirFactura($idVenta);
            if ($result['success']) {
                http_response_code(201);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de venta requerido en la URL para emitir factura (ej: /facturas/{idVenta}).']);
        }
        break;

    case 'PUT':
    case 'DELETE':
        http_response_code(501); // Not Implemented
        echo json_encode(['success' => false, 'message' => 'Método no implementado para facturas.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método HTTP no permitido para este recurso.']);
        break;
}
