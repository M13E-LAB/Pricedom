<?php
echo "🔧 FIX ENV RAILWAY - Force configuration\n";
echo "=======================================\n\n";

// 1. Vérifier et afficher l'état actuel
echo "1️⃣  ÉTAT ACTUEL\n";
echo "-------------\n";

$current_env = [
    'APP_ENV' => getenv('APP_ENV') ?: 'NOT_SET',
    'APP_DEBUG' => getenv('APP_DEBUG') ?: 'NOT_SET',
    'APP_KEY' => getenv('APP_KEY') ? 'SET (' . substr(getenv('APP_KEY'), 0, 10) . '...)' : 'NOT_SET',
    'DATABASE_URL' => getenv('DATABASE_URL') ? 'SET (' . substr(getenv('DATABASE_URL'), 0, 30) . '...)' : 'NOT_SET',
    'DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'NOT_SET',
    'PORT' => getenv('PORT') ?: 'NOT_SET'
];

foreach ($current_env as $key => $value) {
    echo "$key: $value\n";
}

// 2. Lire le fichier .env local s'il existe
echo "\n2️⃣  FICHIER .ENV LOCAL\n";
echo "--------------------\n";

if (file_exists('.env')) {
    echo "✅ .env trouvé, analyse...\n";
    
    $env_content = file_get_contents('.env');
    $lines = explode("\n", $env_content);
    
    $env_vars = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && !str_starts_with($line, '#') && str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($value);
        }
    }
    
    // Afficher les variables importantes
    $important_vars = ['APP_KEY', 'DB_CONNECTION', 'DATABASE_URL', 'APP_ENV'];
    foreach ($important_vars as $var) {
        if (isset($env_vars[$var])) {
            $display_value = in_array($var, ['APP_KEY', 'DATABASE_URL']) 
                ? substr($env_vars[$var], 0, 15) . '...' 
                : $env_vars[$var];
            echo "  $var: $display_value\n";
        } else {
            echo "  $var: NOT_SET\n";
        }
    }
} else {
    echo "❌ .env manquant\n";
}

// 3. Forcer les bonnes variables pour Railway
echo "\n3️⃣  FORÇAGE CONFIGURATION RAILWAY\n";
echo "--------------------------------\n";

// Configuration PostgreSQL pour Railway
$railway_config = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'DB_CONNECTION' => 'pgsql',
    'DATABASE_URL' => getenv('DATABASE_URL') ?: 'postgresql://postgres:ARONEEXyEVmyfNShkXDBAyDSoFswjSAv@postgres.railway.internal:5432/railway'
];

foreach ($railway_config as $key => $value) {
    putenv("$key=$value");
    echo "✅ $key configuré\n";
}

// 4. Test de la configuration PostgreSQL
echo "\n4️⃣  TEST POSTGRESQL\n";
echo "-----------------\n";

try {
    $database_url = getenv('DATABASE_URL');
    echo "URL: " . substr($database_url, 0, 50) . "...\n";
    
    $pdo = new PDO($database_url, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ Connexion PostgreSQL OK\n";
    
    // Test de requête simple
    $stmt = $pdo->query("SELECT version(), current_database(), current_user");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database: " . $info['current_database'] . "\n";
    echo "✅ User: " . $info['current_user'] . "\n";
    
    // Vérifier les tables existantes
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Tables trouvées: " . count($tables) . "\n";
    
    if (empty($tables)) {
        echo "⚠️  Aucune table - migrations nécessaires\n";
    } else {
        echo "   Tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur PostgreSQL: " . $e->getMessage() . "\n";
}

// 5. Test Laravel avec la nouvelle config
echo "\n5️⃣  TEST LARAVEL\n";
echo "---------------\n";

try {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "✅ Autoloader OK\n";
        
        // Charger l'application Laravel
        $app = require_once 'bootstrap/app.php';
        echo "✅ Application Laravel créée\n";
        
        // Initialiser le kernel
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        echo "✅ Kernel initialisé\n";
        
        // Test de la configuration database
        $db_config = config('database.connections.pgsql');
        echo "✅ Config database chargée\n";
        
    } else {
        echo "❌ vendor/autoload.php manquant\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur Laravel: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🏁 FIX TERMINÉ\n";
echo "=============\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
?> 