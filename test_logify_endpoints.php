<?php

/**
 * Test script to verify Logify API endpoints are working
 */

echo "Testing Logify API Endpoints...\n\n";

// Test endpoints
$endpoints = [
    'Login System Health' => 'http://localhost:8000',
    'Logify System Health' => 'http://localhost:8002',
    'Login API User Endpoint' => 'http://localhost:8000/api/user',
];

function testEndpoint($name, $url, $headers = []) {
    echo "Testing: $name\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ CURL Error: $error\n";
        return false;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ Success (HTTP $httpCode)\n";
        if ($httpCode == 200 && !empty($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "📄 Response: " . substr(json_encode($decoded, JSON_PRETTY_PRINT), 0, 200) . "...\n";
            } else {
                echo "📄 Response: " . substr($response, 0, 200) . "...\n";
            }
        }
        return true;
    } else {
        echo "❌ Failed (HTTP $httpCode)\n";
        if (!empty($response)) {
            echo "📄 Response: " . substr($response, 0, 200) . "...\n";
        }
        return false;
    }
}

// Test basic endpoints
foreach ($endpoints as $name => $url) {
    testEndpoint($name, $url);
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "Test completed!\n";
