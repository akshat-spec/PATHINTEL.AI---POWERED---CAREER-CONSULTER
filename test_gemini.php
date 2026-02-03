<?php
/**
 * Test Gemini API Connection
 * Open this file in browser: http://localhost/career_guidance/test_gemini.php
 */

// Include configuration
require_once 'config/gemini_config.php';

echo "<h1>Gemini API Test</h1>";

// Check if cURL is enabled
if (!function_exists('curl_init')) {
    echo "<p style='color: red;'>❌ cURL is NOT enabled in PHP. Please enable it in php.ini</p>";
    exit;
}
echo "<p style='color: green;'>✅ cURL is enabled</p>";

// Test API call
echo "<h2>Testing API Connection...</h2>";

$testMessage = "Hello, please respond with 'API connection successful'";

$geminiUrl = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;

$requestBody = [
    'contents' => [
        [
            'parts' => [
                ['text' => $testMessage]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 100,
    ]
];

$ch = curl_init($geminiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<h3>Results:</h3>";
echo "<p><strong>HTTP Code:</strong> " . $httpCode . "</p>";

if ($curlError) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> " . $curlError . "</p>";
}

echo "<h3>API Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS! AI Response: " . 
             htmlspecialchars($responseData['candidates'][0]['content']['parts'][0]['text']) . 
             "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Got response but format is unexpected</p>";
    }
} else {
    echo "<p style='color: red;'>❌ API call failed with HTTP code: " . $httpCode . "</p>";
    
    $errorData = json_decode($response, true);
    if (isset($errorData['error'])) {
        echo "<p><strong>Error Details:</strong> " . htmlspecialchars(json_encode($errorData['error'], JSON_PRETTY_PRINT)) . "</p>";
    }
}

// Show configuration (hide partial API key)
echo "<h3>Configuration:</h3>";
echo "<p><strong>API URL:</strong> " . GEMINI_API_URL . "</p>";
echo "<p><strong>API Key:</strong> " . substr(GEMINI_API_KEY, 0, 10) . "..." . substr(GEMINI_API_KEY, -5) . "</p>";
?>
