<?php

echo "=== ADDING G16_CAPSTONE DATABASE CONFIG ===\n\n";

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ .env file not found!\n";
    exit(1);
}

// Read current .env content
$envContent = file_get_contents($envFile);

// Check if G16_CAPSTONE config already exists
if (strpos($envContent, 'G16_DB_HOST') !== false) {
    echo "✅ G16_CAPSTONE configuration already exists in .env file\n";
} else {
    // Add G16_CAPSTONE configuration
    $g16Config = "\n# G16_CAPSTONE Database Connection\n";
    $g16Config .= "G16_DB_HOST=127.0.0.1\n";
    $g16Config .= "G16_DB_PORT=3306\n";
    $g16Config .= "G16_DB_DATABASE=pn_system\n";
    $g16Config .= "G16_DB_USERNAME=root\n";
    $g16Config .= "G16_DB_PASSWORD=root\n";
    
    // Append to .env file
    file_put_contents($envFile, $envContent . $g16Config);
    
    echo "✅ Added G16_CAPSTONE database configuration to .env file\n";
    echo "Added:\n";
    echo "G16_DB_HOST=127.0.0.1\n";
    echo "G16_DB_PORT=3306\n";
    echo "G16_DB_DATABASE=pn_system\n";
    echo "G16_DB_USERNAME=root\n";
    echo "G16_DB_PASSWORD=root\n";
}

echo "\n🎯 Now test the integration:\n";
echo "1. Go to G16_CAPSTONE and mark a task as 'Invalid'\n";
echo "2. Visit: http://localhost:8004/educator/violation\n";
echo "3. You should see the invalid student in the violations table!\n";

echo "\n=== CONFIGURATION COMPLETE ===\n";
