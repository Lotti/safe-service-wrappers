<?php

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';

// Target endpoint
$targetPath = '/AIMWebService/api/Accounts';

header('Content-Type: application/json');
// Check if the request matches the target endpoint and method
if ($requestMethod === 'GET' && strpos($requestUri, $targetPath) === 0) {
    // Log received query parameters (optional, for debugging)
    // file_put_contents('mock_server.log', 'Received request: ' . $requestUri . "\n", FILE_APPEND);
    if (isset($_GET['Name'])) {
      switch($_GET['Name']) {
        case 'cacheuser':
          http_response_code(200);
          echo json_encode(['Content' => 'cache1234']);
          exit;
        case 'mariauser':
          http_response_code(200);
          echo json_encode(['Content' => 'mariapassword']);
          exit;
        case 'oracleuser':
          http_response_code(200);
          echo json_encode(['Content' => 'oraclepassword']);
          exit;
        case 'mongouser':
          http_response_code(200);
          echo json_encode(['Content' => 'mongopassword']);
          exit;
        case 'redisuser':
          http_response_code(200);
          echo json_encode(['Content' => 'redispassword']);
          exit;
        default:
          http_response_code(200);
          echo json_encode(['Content' => 'default1234']);
          exit;
      }
    }
}

// If no route matched, return 404
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
exit;
