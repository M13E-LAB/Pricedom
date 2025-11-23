<?php
echo "🚂 RAILWAY FIX - Setting up Zyma with PostgreSQL...\n";

require_once 'vendor/autoload.php';

try {
    // Bootstrap Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "✅ Laravel loaded\n";

    // Configure PostgreSQL environment variables
    putenv('DB_CONNECTION=pgsql');
    putenv('DATABASE_URL=postgresql://postgres:ARONEEXyEVmyfNShkXDBAyDSoFswjSAv@postgres.railway.internal:5432/railway');
    
    // Update config to use PostgreSQL
    config(['database.default' => 'pgsql']);
    
    echo "🐘 PostgreSQL configuration set\n";

    // Run migrations
    echo "📊 Running migrations...\n";
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "✅ Migrations done\n";

    // Create users
    echo "👤 Creating users...\n";
    
    // Admin user
    $admin = \App\Models\User::firstOrCreate(
        ['email' => 'admin@zyma.com'],
        [
            'name' => 'Admin Zyma',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'email_verified_at' => now()
        ]
    );
    echo "✅ Admin: {$admin->email}\n";
    
    // Test user
    $test = \App\Models\User::firstOrCreate(
        ['email' => 'test@test.com'],
        [
            'name' => 'Test User',
            'password' => \Illuminate\Support\Facades\Hash::make('test123'),
            'email_verified_at' => now()
        ]
    );
    echo "✅ Test: {$test->email}\n";

    // Verify auth works
    echo "🔐 Testing auth...\n";
    if (\Illuminate\Support\Facades\Auth::attempt(['email' => 'admin@zyma.com', 'password' => 'password123'])) {
        echo "✅ Auth works!\n";
    } else {
        echo "❌ Auth failed\n";
    }

    echo "\n🎉 SETUP COMPLETE!\n";
    echo "📧 Login: admin@zyma.com / password123\n";
    echo "📧 Test: test@test.com / test123\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?> 