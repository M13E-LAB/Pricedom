<?php
echo "🐘 POSTGRESQL SETUP - Migrating from SQLite to PostgreSQL...\n";

require_once 'vendor/autoload.php';

try {
    // Bootstrap Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "✅ Laravel loaded\n";

    // Set environment variables for PostgreSQL
    putenv('DB_CONNECTION=pgsql');
    putenv('DATABASE_URL=postgresql://postgres:ARONEEXyEVmyfNShkXDBAyDSoFswjSAv@postgres.railway.internal:5432/railway');
    putenv('DB_HOST=postgres.railway.internal');
    putenv('DB_PORT=5432');
    putenv('DB_DATABASE=railway');
    putenv('DB_USERNAME=postgres');
    putenv('DB_PASSWORD=ARONEEXyEVmyfNShkXDBAyDSoFswjSAv');

    // Reload config to pick up new database settings
    app()->make('config')->set('database.default', 'pgsql');
    
    echo "✅ PostgreSQL configuration loaded\n";

    // Test database connection
    try {
        $pdo = new PDO(
            'pgsql:host=postgres.railway.internal;port=5432;dbname=railway',
            'postgres',
            'ARONEEXyEVmyfNShkXDBAyDSoFswjSAv',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✅ PostgreSQL connection successful\n";
    } catch (Exception $e) {
        echo "❌ PostgreSQL connection failed: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Drop all tables to start fresh (in case there are old tables)
    echo "🗄️ Preparing clean database...\n";
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:reset', ['--force' => true]);
        echo "✅ Database cleaned\n";
    } catch (Exception $e) {
        echo "ℹ️ No existing tables to clean (this is fine)\n";
    }

    // Run migrations
    echo "📊 Running migrations on PostgreSQL...\n";
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "✅ All migrations completed on PostgreSQL\n";

    // Create test users
    echo "👤 Creating test users...\n";
    
    // Admin user
    $admin = \App\Models\User::firstOrCreate(
        ['email' => 'admin@zyma.com'],
        [
            'name' => 'Admin Zyma',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'email_verified_at' => now()
        ]
    );
    echo "✅ Admin created: {$admin->email} (ID: {$admin->id})\n";
    
    // Test user
    $test = \App\Models\User::firstOrCreate(
        ['email' => 'test@test.com'],
        [
            'name' => 'Test User',
            'password' => \Illuminate\Support\Facades\Hash::make('test123'),
            'email_verified_at' => now()
        ]
    );
    echo "✅ Test user created: {$test->email} (ID: {$test->id})\n";

    // Verify persistence by counting users
    $userCount = \App\Models\User::count();
    echo "📊 Total users in database: $userCount\n";

    // Test authentication
    echo "🔐 Testing authentication...\n";
    if (\Illuminate\Support\Facades\Auth::attempt(['email' => 'admin@zyma.com', 'password' => 'password123'])) {
        echo "✅ Authentication works!\n";
        \Illuminate\Support\Facades\Auth::logout();
    } else {
        echo "❌ Authentication failed\n";
    }

    // Test user creation and persistence
    echo "🧪 Testing user creation and persistence...\n";
    $testUser = \App\Models\User::create([
        'name' => 'Persistence Test',
        'email' => 'persistence@test.com',
        'password' => \Illuminate\Support\Facades\Hash::make('testpassword'),
        'email_verified_at' => now()
    ]);
    
    // Immediately try to find the user again
    $foundUser = \App\Models\User::where('email', 'persistence@test.com')->first();
    if ($foundUser) {
        echo "✅ User persistence test PASSED - User ID: {$foundUser->id}\n";
        // Clean up test user
        $foundUser->delete();
        echo "✅ Test user cleaned up\n";
    } else {
        echo "❌ User persistence test FAILED\n";
    }

    // Final verification
    $finalCount = \App\Models\User::count();
    echo "📊 Final user count: $finalCount\n";

    echo "\n🎉 POSTGRESQL SETUP COMPLETE!\n";
    echo "🔗 Database: PostgreSQL on Railway\n";
    echo "📧 Admin Login: admin@zyma.com / password123\n";
    echo "📧 Test Login: test@test.com / test123\n";
    echo "✅ User accounts will now persist reliably!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?> 