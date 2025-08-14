<?php
declare(strict_types=1);

// Mostrar errores en desarrollo
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Definir la ruta base del proyecto (sube desde backend a sgc-project)
define('BASE_DIR', dirname(__DIR__));

// Autocargador de clases (solo para Entities por ahora)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/Entities/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    // Manejo para App\Entities\Nombre → Entities/Nombre.php
    if (strpos($relative_class, 'Entities\\') === 0) {
        $relative_class = substr($relative_class, strlen('Entities\\'));
    } else {
        return;
    }

    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Cabeceras CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Definir base de la API (ajustar según tu estructura de carpetas si cambia)
define('API_BASE_PATH', '/sgc-project/backend');

// Parsear la URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = str_replace(API_BASE_PATH, '', $requestUri);
$segments = explode('/', trim($requestUri, '/'));

// Obtener recurso
$resource = $segments[0] ?? '';

// Enrutar según el recurso
switch ($resource) {
    case 'clientes':
        require __DIR__ . '/api/clientes.php';
        break;
    case 'productos':
        require __DIR__ . '/api/productos.php';
        break;
    case 'ventas':
        require __DIR__ . '/api/ventas.php';
        break;
    case 'facturas':
        require __DIR__ . '/api/facturas.php';
        break;
    case '':
        http_response_code(200);
        echo json_encode(['message' => 'Bienvenido a la API de SGC.']);
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Recurso no encontrado: ' . $resource]);
        break;
}
