<?php

// Simple router for the mock CyberArk AIM service

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';

// Target endpoint
$targetPath = '/AIMWebService/api/Accounts';

// Check if the request matches the target endpoint and method
if ($requestMethod === 'GET' && strpos($requestUri, $targetPath) === 0) {
  // Log received query parameters (optional, for debugging)
  // file_put_contents('mock_server.log', 'Received request: ' . $requestUri . "\n", FILE_APPEND);
  if (isset($_GET['Name'])) {
    $credentials = [
      ['user' => 'maria', 'password' => 'maria1234'],
      ['user' => 'redis', 'password' => 'redis1234']
    ];

    foreach ($credentials as $c) {
      if ($_GET['Name'] === $c['user']) {
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(['Content' => $c['password']]);
        exit;
      }
    }
  }
}

// If no route matched, return 404
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
exit;
