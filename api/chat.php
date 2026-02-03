<?php
/**
 * Gemini Chat API Endpoint
 * Handles chat requests and communicates with Gemini API
 */

// Start session for rate limiting
session_start();

// Include configuration
require_once '../config/gemini_config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['message']) || empty(trim($data['message']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit;
}

$userMessage = trim($data['message']);

// Simple rate limiting
if (!isset($_SESSION['chat_requests'])) {
    $_SESSION['chat_requests'] = [];
}

// Clean old requests outside the time window
$currentTime = time();
$_SESSION['chat_requests'] = array_filter($_SESSION['chat_requests'], function($timestamp) use ($currentTime) {
    return ($currentTime - $timestamp) < RATE_LIMIT_WINDOW;
});

// Check rate limit
if (count($_SESSION['chat_requests']) >= RATE_LIMIT_REQUESTS) {
    http_response_code(429);
    echo json_encode([
        'success' => false, 
        'error' => 'Rate limit exceeded. Please try again later.'
    ]);
    exit;
}

// Add current request to tracking
$_SESSION['chat_requests'][] = $currentTime;

// Prepare request to Gemini API
$geminiUrl = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;

$requestBody = [
    'contents' => [
        [
            'parts' => [
                ['text' => GEMINI_SYSTEM_PROMPT],
                ['text' => "User: " . $userMessage]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => GEMINI_TEMPERATURE,
        'topK' => GEMINI_TOP_K,
        'topP' => GEMINI_TOP_P,
        'maxOutputTokens' => GEMINI_MAX_TOKENS,
    ]
];

// Make API request using cURL
$ch = curl_init($geminiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Disable SSL verification for XAMPP/Localhost
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Handle API errors
if ($httpCode !== 200) {
    // Get curl error if any
    $curlError = curl_error($ch);
    
    http_response_code(500);
    $errorMessage = 'Failed to get response from AI. Please try again.';
    if ($httpCode === 429) {
        $errorMessage = 'High traffic (Rate Limit Exceeded). Please try again in a minute.';
    }

    echo json_encode([
        'success' => false, 
        'error' => $errorMessage,
        'debug' => [
            'http_code' => $httpCode,
            'curl_error' => $curlError,
            'response' => substr($response, 0, 500) // First 500 chars of response
        ]
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Parse response
$responseData = json_decode($response, true);
$aiReply = "I'm sorry, I couldn't understand that.";

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiReply = $responseData['candidates'][0]['content']['parts'][0]['text'];
} else {
    // Log unexpected response structure
    error_log("Unexpected Gemini API response: " . substr($response, 0, 1000));
}

// Return success response
echo json_encode([
    'success' => true,
    'reply' => $aiReply,
    'timestamp' => time()
]);
?>
