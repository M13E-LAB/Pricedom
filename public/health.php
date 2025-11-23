<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$health = [
    'status' => 'checking',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

try {
    // 1. Test PHP basique
    $health['checks']['php'] = [
        'status' => 'ok',
        'version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'upload_max' => ini_get('upload_max_filesize')
    ];

    // 2. Test des extensions PHP
    $required_extensions = ['pdo', 'pdo_pgsql', 'mbstring', 'openssl'];
    $extensions_ok = true;
    $extensions_status = [];
    
    foreach ($required_extensions as $ext) {
        $loaded = extension_loaded($ext);
        $extensions_status[$ext] = $loaded ? 'loaded' : 'missing';
        if (!$loaded) $extensions_ok = false;
    }
    
    $health['checks']['extensions'] = [
        'status' => $extensions_ok ? 'ok' : 'error',
        'details' => $extensions_status
    ];

    // 3. Test des variables d'environnement
    $env_vars = ['DATABASE_URL', 'APP_KEY', 'PORT'];
    $env_ok = true;
    $env_status = [];
    
    foreach ($env_vars as $var) {
        $value = getenv($var);
        if ($value !== false && !empty($value)) {
            $env_status[$var] = 'set';
        } else {
            $env_status[$var] = 'missing';
            $env_ok = false;
        }
    }
    
    $health['checks']['environment'] = [
        'status' => $env_ok ? 'ok' : 'error',
        'details' => $env_status
    ];

    // 4. Test de la base de données
    try {
        $database_url = getenv('DATABASE_URL');
        if ($database_url && strpos($database_url, 'postgresql://') === 0) {
            $pdo = new PDO($database_url, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3
            ]);
            $stmt = $pdo->query("SELECT 1");
            $health['checks']['database'] = [
                'status' => 'ok',
                'type' => 'postgresql',
                'connection' => 'success'
            ];
        } else {
            $health['checks']['database'] = [
                'status' => 'error',
                'error' => 'DATABASE_URL missing or invalid'
            ];
        }
    } catch (Exception $e) {
        $health['checks']['database'] = [
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }

    // 5. Test des fichiers Laravel
    $laravel_files = [
        'vendor/autoload.php',
        'bootstrap/app.php',
        'public/index.php'
    ];
    
    $files_ok = true;
    $files_status = [];
    
    foreach ($laravel_files as $file) {
        if (file_exists('../' . $file)) {
            $files_status[$file] = 'exists';
        } else {
            $files_status[$file] = 'missing';
            $files_ok = false;
        }
    }
    
    $health['checks']['files'] = [
        'status' => $files_ok ? 'ok' : 'error',
        'details' => $files_status
    ];

    // 6. Test de chargement Laravel simple
    try {
        if (file_exists('../vendor/autoload.php')) {
            require_once '../vendor/autoload.php';
            $health['checks']['laravel_load'] = [
                'status' => 'ok',
                'autoloader' => 'loaded'
            ];
        } else {
            $health['checks']['laravel_load'] = [
                'status' => 'error',
                'error' => 'autoloader not found'
            ];
        }
    } catch (Exception $e) {
        $health['checks']['laravel_load'] = [
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }

    // Déterminer le statut global
    $all_ok = true;
    foreach ($health['checks'] as $check) {
        if ($check['status'] !== 'ok') {
            $all_ok = false;
            break;
        }
    }
    
    $health['status'] = $all_ok ? 'healthy' : 'unhealthy';
    
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['error'] = $e->getMessage();
}

echo json_encode($health, JSON_PRETTY_PRINT);
?> 