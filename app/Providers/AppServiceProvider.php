<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Railway provides HTTPS automatically, just configure Laravel to understand it
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        // 🐘 FORCE POSTGRESQL ABSOLUTELY - No more SQLite!
        // DISABLED FOR LOCAL DEVELOPMENT
        // $this->forcePostgreSQL();
    }

    /**
     * Force PostgreSQL configuration - Railway compatible
     */
    private function forcePostgreSQL(): void
    {
        try {
            // Force environment variables
            $_ENV['DB_CONNECTION'] = 'pgsql';
            $_ENV['DATABASE_URL'] = 'postgresql://postgres:ARONEEXyEVmyfNShkXDBAyDSoFswjSAv@postgres.railway.internal:5432/railway';
            
            // Set putenv for PHP
            putenv('DB_CONNECTION=pgsql');
            putenv('DATABASE_URL=postgresql://postgres:ARONEEXyEVmyfNShkXDBAyDSoFswjSAv@postgres.railway.internal:5432/railway');
            
            // Force Laravel config
            Config::set('database.default', 'pgsql');
            Config::set('database.connections.default', Config::get('database.connections.pgsql'));
            
            // Purge existing SQLite connections
            DB::purge('sqlite');
            
            // Force reconnect with PostgreSQL
            DB::reconnect();
            
        } catch (\Exception $e) {
            // Silent fail - continue with whatever config exists
            \Log::warning('🐘 PostgreSQL force config failed: ' . $e->getMessage());
        }
    }
}
