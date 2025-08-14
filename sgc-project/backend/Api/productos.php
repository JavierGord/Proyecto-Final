<?php
declare(strict_types=1);

namespace App\Api;

// Incluir las dependencias necesarias
// Nota: En un proyecto real con Composer, usarías 'require_once __DIR__ . '/../../vendor/autoload.php';'
// y los 'use' statements serían suficientes. Para este enfoque manual, incluimos explícitamente.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ClienteService.php';
require_once __DIR__ . '/../entities/Cliente.php';
require_once __DIR__ . '/../entities/PersonaNatural.php';
require_once __DIR__ . '/../entities/PersonaJuridica.php';

use App\Services\ClienteService;

// Configuración de cabeceras CORS (Cross-Origin Resource Sharing)
// Esto permite que tu frontend (que probablemente corre en un dominio/puerto diferente)
// pueda hacer solicitudes a tu API.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite cualquier origen (para desarrollo). En producción, especifica tu dominio.
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // Métodos HTTP permitidos
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // Cabeceras permitidas

$method = $_SERVER['REQUEST_METHOD']; // Obtiene el método HTTP de la solicitud (GET, POST, PUT, DELETE, OPTIONS)
$clienteService = new ClienteService(); // Instancia el servicio de clientes

// Manejar solicitudes OPTIONS (preflight CORS)
// Los navegadores envían una solicitud OPTIONS antes de una solicitud "real" (POST, PUT, DELETE)
// para verificar los permisos CORS.
if ($method === 'OPTIONS') {
    http_response_code(200); // Responde con 200 OK para indicar que los métodos están permitidos
    exit(); // Termina la ejecución
}

// Lógica de enrutamiento basada en el método HTTP
switch ($method) {
    case 'GET':
        // Manejar solicitudes GET
        if (isset($_GET['id'])) {
            // Si se proporciona un ID, buscar un cliente específico
            $id = (int)$_GET['id'];
            $cliente = $clienteService->obtenerClientePorId($id);
            if ($cliente) {
                echo json_encode(['success' => true, 'data' => $cliente]);
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['success' => false, 'message' => 'Cliente no encontrado.']);
            }
        } elseif (isset($_GET['query'])) {
            // Si se proporciona una query, buscar clientes por esa query
            $query = $_GET['query'];
            $clientes = $clienteService->buscarClientes($query);
            echo json_encode(['success' => true, 'data' => $clientes]);
        } else {
            // Si no se proporciona ID ni query, devolver un error o listar todos (si se implementa)
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Parámetros de búsqueda inválidos o faltantes.']);
        }
        break;

    case 'POST':
        // Manejar solicitudes POST (crear un nuevo cliente)
        $data = json_decode(file_get_contents('php://input'), true); // Obtiene el cuerpo de la solicitud JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'JSON inválido en el cuerpo de la solicitud.']);
            break;
        }
        $result = $clienteService->crearCliente($data); // Llama al servicio para crear el cliente
        if ($result['success']) {
            http_response_code(201); // Created
            echo json_encode($result);
        } else {
            http_response_code(400); // Bad Request
            echo json_encode($result);
        }
        break;

    case 'PUT':
        // Manejar solicitudes PUT (actualizar un cliente existente)
        // Se espera que el ID del cliente esté en la URL (ej: /api/clientes/123)
        // El ID se obtiene del enrutador principal (backend/index.php)
        $pathSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $id = (int)end($pathSegments); // El último segmento de la URL debería ser el ID

        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'JSON inválido en el cuerpo de la solicitud.']);
            break;
        }
        $result = $clienteService->actualizarCliente($id, $data);
        if ($result['success']) {
            http_response_code(200); // OK
            echo json_encode($result);
        } else {
            http_response_code(400); // Bad Request
            echo json_encode($result);
        }
        break;

    case 'DELETE':
        // Manejar solicitudes DELETE (eliminar un cliente)
        // Se espera que el ID del cliente esté en la URL (ej: /api/clientes/123)
        $pathSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $id = (int)end($pathSegments); // El último segmento de la URL debería ser el ID

        $result = $clienteService->eliminarCliente($id);
        if ($result['success']) {
            http_response_code(200); // OK
            echo json_encode($result);
        } else {
            http_response_code(400); // Bad Request
            echo json_encode($result);
        }
        break;

    default:
        // Método HTTP no permitido
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Método HTTP no permitido para este recurso.']);
        break;
}
