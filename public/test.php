<?php
// Simple test to check if PHP is working
echo "PHP is working!\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "PHP Version: " . phpversion() . "\n";

// Test Laravel bootstrap
try {
    require __DIR__ . '/../bootstrap/app.php';
    echo "Laravel bootstrap successful!\n";
} catch (Exception $e) {
    echo "Laravel bootstrap failed: " . $e->getMessage() . "\n";
}