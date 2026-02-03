<?php
// Include config file
require_once __DIR__ . '/../config.php';

// Function to call Python model via cURL
function callPythonModel($data) {
    $url = PYTHON_MODEL_URL;

    // Initialize cURL
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Assuming form data
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout in seconds

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        error_log("cURL error: " . $error_msg, 3, __DIR__ . '/../logs/app.log');
        return ['error' => 'Failed to connect to prediction service. Please try again later.'];
    }

    // Get HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("Python model returned HTTP $http_code", 3, __DIR__ . '/../logs/app.log');
        return ['error' => 'Prediction service is currently unavailable. Please try again later.'];
    }

    // Decode JSON response
    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg(), 3, __DIR__ . '/../logs/app.log');
        return ['error' => 'Invalid response from prediction service.'];
    }

    return $decoded_response;
}

// Handle POST request to this script
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $input_data = [];
    foreach ($_POST as $key => $value) {
        $input_data[$key] = htmlspecialchars(trim($value));
    }

    // Call Python model
    $result = callPythonModel($input_data);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
} else {
    // Handle non-POST requests
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
?>
