<?php
echo "🧪 Testing PostgreSQL connection...\n";

try {
    $pdo = new PDO(
        'pgsql:host=postgres.railway.internal;port=5432;dbname=railway',
        'postgres',
        'ARONEEXyEVmyfNShkXDBAyDSoFswjSAv',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ PostgreSQL connection successful\n";
    
    // Test basic query
    $result = $pdo->query("SELECT version()");
    $version = $result->fetchColumn();
    echo "📊 PostgreSQL version: $version\n";
    
    // Test table existence
    $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tables found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?> 