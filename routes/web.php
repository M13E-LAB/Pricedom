<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OpenFoodFactsController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContributeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

// Route d'accueil - Redirection vers la page de connexion normale
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Authentication routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Routes pour les utilisateurs non connectés
// HomeController removed - route conflict fixed

// 🚨 ROUTE D'URGENCE - Connexion automatique
Route::get('/emergency-login', function () {
    try {
        // Find or create admin user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@zyma.com'],
            [
                'name' => 'Admin Zyma',
                'password' => \Hash::make('password123'),
                'email_verified_at' => now()
            ]
        );
        
        // Force login
        \Auth::login($user);
        
        return redirect()->route('products.search')->with('success', '🎉 Connexion d\'urgence réussie !');
        
    } catch (Exception $e) {
        return '<h1 style="color: red;">Emergency Login Failed: ' . $e->getMessage() . '</h1>';
    }
});

// Routes protégées par l'authentification
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $totalContributions = $user->contributions()->count();
        $recentContributions = $user->contributions()->latest()->take(5)->get();
        $badges = $user->badges()->with('badge')->get();
        
        return view('dashboard', compact('totalContributions', 'recentContributions', 'badges'));
    })->name('dashboard');
    
    // Dashboard des prix
    Route::get('/prices-dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('prices.dashboard');
    Route::get('/prices-dashboard/export', [\App\Http\Controllers\DashboardController::class, 'exportExcel'])->name('prices.export');

    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/products/details/{id}', [ProductController::class, 'details'])->name('products.details');
    
    // Routes pour le volet Prix - consultation de la base Open Prices
    Route::get('/prices/browse', [OpenFoodFactsController::class, 'browse'])->name('prices.browse');
    Route::get('/prices/search', [OpenFoodFactsController::class, 'searchPrices'])->name('prices.search');
    Route::get('/prices/coverage', function () {
        return view('prices.coverage');
    })->name('prices.coverage');
    
    // Route de debug pour la géolocalisation
    Route::get('/debug/geolocation', function () {
        return view('debug.geolocation');
    })->name('debug.geolocation');
    
    // Route de debug pour tester l'API directement
    Route::get('/debug/api-test', function (Request $request) {
        $country = $request->get('country', 'France');
        
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(30)->get('https://prices.openfoodfacts.org/api/v1/prices', [
                'osm_address_country__like' => $country,
                'size' => 10,
                'order_by' => '-date'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $items = $data['items'] ?? [];
                
                $output = [];
                $output[] = "🔍 TEST API DIRECT - Pays: {$country}";
                $output[] = "===============================================";
                $output[] = "Total résultats: " . ($data['total'] ?? 0);
                $output[] = "Résultats retournés: " . count($items);
                $output[] = "";
                
                foreach (array_slice($items, 0, 5) as $i => $item) {
                    $location = $item['location'] ?? [];
                    $output[] = "#{$i} - " . ($item['product_name'] ?? 'Produit inconnu');
                    $output[] = "  🏪 Magasin: " . ($location['osm_name'] ?? 'N/A');
                    $output[] = "  🌍 Ville: " . ($location['osm_address_city'] ?? 'N/A');
                    $output[] = "  🏳️ Pays: " . ($location['osm_address_country'] ?? 'N/A');
                    $output[] = "  💰 Prix: " . ($item['price'] ?? 'N/A') . "€";
                    $output[] = "";
                }
                
                return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
                       implode("\n", $output) . 
                       '</pre><br>' .
                       '<a href="/debug/api-response" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔄 Autres tests</a>';
            } else {
                return '<h1 style="color: red;">Erreur API: ' . $response->status() . '</h1>';
            }
        } catch (\Exception $e) {
            return '<h1 style="color: red;">Exception: ' . $e->getMessage() . '</h1>';
        }
    })->name('debug.api-test');
    
    Route::get('/debug/api-response', function () {
        return view('debug.api-response');
    })->name('debug.api-response');
    
    Route::get('/social', [SocialController::class, 'index'])->name('social.index');
    Route::get('/social/create', [SocialController::class, 'create'])->name('social.create');
    Route::post('/social', [SocialController::class, 'store'])->name('social.store');
    Route::post('/social/like/{post}', [SocialController::class, 'like'])->name('social.like');
    Route::get('/social/my-posts', [SocialController::class, 'myPosts'])->name('social.my-posts');
    Route::delete('/social/{post}', [SocialController::class, 'destroy'])->name('social.destroy');
    
    Route::get('/contribute', [ContributeController::class, 'index'])->name('contribute.index');
    Route::post('/contribute', [ContributeController::class, 'store'])->name('contribute.store');
    Route::get('/contribute/badges', [ContributeController::class, 'badges'])->name('contribute.badges');
    Route::get('/contribute/scan', [ContributeController::class, 'scan'])->name('contribute.scan');
    Route::post('/contribute/scan-ticket', [ContributeController::class, 'scanTicket'])->name('contribute.scan-ticket');
    Route::post('/contribute/store-bulk', [ContributeController::class, 'storeBulk'])->name('contribute.store-bulk');
    Route::get('/contribute/manual', [ContributeController::class, 'manual'])->name('contribute.manual');

    // Routes pour la ligue healthy
    Route::get('/league', [LeagueController::class, 'index'])->name('league.index');
    Route::get('/league/rankings', [LeagueController::class, 'rankings'])->name('league.rankings');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Routes admin dashboard - vérification dans le contrôleur
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/export/users', [AdminController::class, 'exportUsers'])->name('export.users');
        Route::get('/export/contributions', [AdminController::class, 'exportContributions'])->name('export.contributions');
        Route::get('/export/products', [AdminController::class, 'exportProducts'])->name('export.products');
        Route::get('/export/social', [AdminController::class, 'exportSocial'])->name('export.social');
        Route::get('/export/posts', [AdminController::class, 'exportPosts'])->name('export.posts');
        Route::get('/export/activity', [AdminController::class, 'exportActivity'])->name('export.activity');
        Route::get('/export/full', [AdminController::class, 'exportFull'])->name('export.full');
    });

    Route::post('/products/fetch', [ProductController::class, 'fetch'])->name('products.fetch');

    // Admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
    
    // 🆕 NOUVEAUX DASHBOARDS SÉPARÉS
    Route::get('/admin/prices', [AdminController::class, 'pricesDashboard'])->name('admin.prices');
    Route::get('/admin/users', [AdminController::class, 'usersDashboard'])->name('admin.users');
    
    Route::get('/admin/contributions', [AdminController::class, 'contributions'])->name('admin.contributions');
    Route::post('/admin/contributions/{id}/verify', [AdminController::class, 'verifyContribution'])->name('admin.contributions.verify');
    
    // Exports existants
    Route::get('/admin/export/excel', [AdminController::class, 'exportExcel'])->name('admin.export.excel');
    Route::get('/admin/export/users', [AdminController::class, 'exportUsers'])->name('admin.export.users');
    Route::get('/admin/export/activity', [AdminController::class, 'exportActivity'])->name('admin.export.activity');
    Route::get('/admin/export/full', [AdminController::class, 'exportFull'])->name('admin.export.full');
    
    // 🆕 NOUVEAUX EXPORTS SPÉCIALISÉS
    Route::get('/admin/export/prices', [AdminController::class, 'exportPrices'])->name('admin.export.prices');
    Route::get('/admin/export/users-data', [AdminController::class, 'exportUsersData'])->name('admin.export.users-data');
    Route::get('/admin/export/stores-stats', [AdminController::class, 'exportStoresStats'])->name('admin.export.stores-stats');

    // Comment routes
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Like routes
    Route::post('/posts/{post}/like', [\App\Http\Controllers\LikeController::class, 'toggle'])->name('posts.like');
    
    // Reaction routes
    Route::post('/posts/{post}/reactions', [ReactionController::class, 'toggle'])->name('reactions.toggle');
    Route::get('/posts/{post}/reactions', [ReactionController::class, 'getReactions'])->name('reactions.get');
});

// Route pour servir les images de posts (contournement du lien symbolique)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    if (file_exists($fullPath)) {
        return response()->file($fullPath);
    }
    
    abort(404);
})->where('path', '.*');

// Route de debug pour vérifier les images
Route::get('/debug-images', function () {
    $output = [];
    $output[] = "🖼️ DEBUG IMAGES ZYMA";
    $output[] = "=====================";
    
    try {
        // Vérifier le lien symbolique
        $symlinkPath = public_path('storage');
        $output[] = "📁 Lien symbolique storage: " . ($symlinkPath);
        $output[] = "🔗 Lien existe: " . (is_link($symlinkPath) ? "✅ OUI" : "❌ NON");
        
        if (is_link($symlinkPath)) {
            $target = readlink($symlinkPath);
            $output[] = "🎯 Cible du lien: " . $target;
            $output[] = "📂 Cible existe: " . (is_dir($target) ? "✅ OUI" : "❌ NON");
        }
        
        // Vérifier le dossier storage/app/public
        $storagePath = storage_path('app/public');
        $output[] = "\n📁 Dossier storage/app/public: " . $storagePath;
        $output[] = "📂 Dossier existe: " . (is_dir($storagePath) ? "✅ OUI" : "❌ NON");
        
        // Vérifier le dossier posts
        $postsPath = storage_path('app/public/posts');
        $output[] = "\n📁 Dossier posts: " . $postsPath;
        $output[] = "📂 Dossier existe: " . (is_dir($postsPath) ? "✅ OUI" : "❌ NON");
        
        if (is_dir($postsPath)) {
            $files = scandir($postsPath);
            $imageFiles = array_filter($files, function($file) {
                return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
            });
            $output[] = "🖼️ Images dans posts: " . count($imageFiles);
            
            if (count($imageFiles) > 0) {
                $output[] = "\n📸 Premières images trouvées:";
                foreach (array_slice($imageFiles, 0, 5) as $file) {
                    $filePath = $postsPath . '/' . $file;
                    $fileSize = filesize($filePath);
                    $output[] = "  - " . $file . " (" . number_format($fileSize/1024, 1) . " KB)";
                    
                    // Tester l'URL
                    $url1 = asset('storage/posts/' . $file);
                    $url2 = url('/storage/posts/' . $file);
                    $output[] = "    URL1: " . $url1;
                    $output[] = "    URL2: " . $url2;
                }
            }
        }
        
        // Vérifier les posts récents
        $output[] = "\n📝 Posts récents avec images:";
        $posts = \App\Models\Post::whereNotNull('image_path')->latest()->take(3)->get();
        foreach ($posts as $post) {
            $imagePath = storage_path('app/public/' . $post->image_path);
            $output[] = "  - Post #{$post->id}: " . $post->image_path;
            $output[] = "    Fichier existe: " . (file_exists($imagePath) ? "✅ OUI" : "❌ NON");
            if (file_exists($imagePath)) {
                $size = filesize($imagePath);
                $output[] = "    Taille: " . number_format($size/1024, 1) . " KB";
            }
        }
        
    } catch (Exception $e) {
        $output[] = "❌ Erreur: " . $e->getMessage();
    }
    
    return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
           implode("\n", $output) . 
           '</pre><br>' .
           '<a href="/social" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🍽️ Retour Social</a>';
});

// Route pour forcer la création du lien symbolique
Route::get('/force-storage-link', function () {
    $output = [];
    $output[] = "🔗 CRÉATION FORCÉE DU LIEN SYMBOLIQUE";
    $output[] = "=====================================";
    
    try {
        $publicStoragePath = public_path('storage');
        $storagePublicPath = storage_path('app/public');
        
        $output[] = "📂 Public path: " . $publicStoragePath;
        $output[] = "📂 Storage path: " . $storagePublicPath;
        
        // Supprimer le lien existant s'il y en a un
        if (file_exists($publicStoragePath) || is_link($publicStoragePath)) {
            if (is_link($publicStoragePath)) {
                unlink($publicStoragePath);
                $output[] = "🗑️ Ancien lien supprimé";
            } else {
                $output[] = "⚠️ Un fichier/dossier existe déjà à cet emplacement";
            }
        }
        
        // Créer le nouveau lien
        if (symlink($storagePublicPath, $publicStoragePath)) {
            $output[] = "✅ Lien symbolique créé avec succès !";
        } else {
            $output[] = "❌ Échec de création du lien symbolique";
        }
        
        // Vérifier le résultat
        $output[] = "\n🔍 VÉRIFICATION :";
        $output[] = "Lien existe: " . (is_link($publicStoragePath) ? "✅ OUI" : "❌ NON");
        
        if (is_link($publicStoragePath)) {
            $target = readlink($publicStoragePath);
            $output[] = "Cible: " . $target;
            $output[] = "Cible valide: " . (is_dir($target) ? "✅ OUI" : "❌ NON");
        }
        
        // Test d'une image
        $testFile = storage_path('app/public/posts/OWSbRRBL7YgycjWfRE6JwTghQPyfCWg2bQSMXXTjpg.jpg');
        if (file_exists($testFile)) {
            $publicUrl = asset('storage/posts/OWSbRRBL7YgycjWfRE6JwTghQPyfCWg2bQSMXXTjpg.jpg');
            $output[] = "\n🖼️ Test image:";
            $output[] = "URL: " . $publicUrl;
            $output[] = "Accessible: " . (file_exists(public_path('storage/posts/OWSbRRBL7YgycjWfRE6JwTghQPyfCWg2bQSMXXTjpg.jpg')) ? "✅ OUI" : "❌ NON");
        }
        
    } catch (Exception $e) {
        $output[] = "❌ Erreur: " . $e->getMessage();
    }
    
    return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
           implode("\n", $output) . 
           '</pre><br>' .
           '<a href="/social" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🍽️ Tester le Feed</a> ' .
           '<a href="/debug-images" style="background: #32cd32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔍 Re-Debug</a>';
});

// 🚀 ROUTE D'INITIALISATION POSTGRESQL
Route::get('/init-production', function () {
    $output = [];
    $output[] = "🚀 INIT PRODUCTION - PostgreSQL + Personal Account";
    $output[] = "===============================================";
    
    try {
        // Force PostgreSQL configuration
        putenv('DB_CONNECTION=pgsql');
        putenv('DATABASE_URL=postgresql://postgres:ARONEEXyEVmyfNShkXDBAyDSoFswjSAv@postgres.railway.internal:5432/railway');
        config(['database.default' => 'pgsql']);
        
        $output[] = "🐘 PostgreSQL configuration forced";

        // Test database connection
        $output[] = "🔗 Testing PostgreSQL connection...";
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $output[] = "✅ PostgreSQL connected!";

        // Run all migrations
        $output[] = "📊 Running migrations...";
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = \Illuminate\Support\Facades\Artisan::output();
        $output[] = $migrationOutput;
        $output[] = "✅ Migrations completed";

        // Create personal account
        $output[] = "👤 Creating personal account...";
        
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'etchelast@gmail.com'],
            [
                'name' => 'Arslane Etchelast',
                'password' => \Illuminate\Support\Facades\Hash::make('motdepasse123'),
                'email_verified_at' => now()
            ]
        );
        
        if ($user->wasRecentlyCreated) {
            $output[] = "✅ Personal account created: {$user->email}";
        } else {
            $output[] = "✅ Personal account already exists: {$user->email}";
        }

        // Create admin account
        $output[] = "👑 Creating admin account...";
        
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@zyma.com'],
            [
                'name' => 'Admin Zyma',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'email_verified_at' => now()
            ]
        );
        
        if ($admin->wasRecentlyCreated) {
            $output[] = "✅ Admin account created: {$admin->email}";
        } else {
            $output[] = "✅ Admin account already exists: {$admin->email}";
        }

        // Test authentication
        $output[] = "🔐 Testing authentication...";
        if (\Illuminate\Support\Facades\Auth::attempt(['email' => 'etchelast@gmail.com', 'password' => 'motdepasse123'])) {
            $output[] = "✅ Personal account auth works!";
            \Illuminate\Support\Facades\Auth::logout();
        } else {
            $output[] = "❌ Personal account auth failed";
        }

        // Check total users
        $totalUsers = \App\Models\User::count();
        $output[] = "👥 Total users in PostgreSQL: {$totalUsers}";

        $output[] = "";
        $output[] = "🎉 PRODUCTION READY!";
        $output[] = "==================";
        $output[] = "🌐 Website: https://zyma05-production.up.railway.app";
        $output[] = "🔑 Login: etchelast@gmail.com / motdepasse123";
        $output[] = "👑 Admin: admin@zyma.com / admin123";
        $output[] = "📝 Register new accounts: https://zyma05-production.up.railway.app/register";
        $output[] = "✅ All accounts are now persistent in PostgreSQL!";

    } catch (Exception $e) {
        $output[] = "❌ Error: " . $e->getMessage();
        $output[] = "Stack trace:";
        $output[] = $e->getTraceAsString();
    }
    
    return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
           implode("\n", $output) . 
           '</pre><br>' .
           '<a href="/login" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔑 Login</a> ' .
           '<a href="/register" style="background: #32cd32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">📝 Register</a> ' .
           '<a href="/social" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🍽️ Social Feed</a>';
});

// 🔍 ROUTE DEBUG DATABASE - Voir quelle base est utilisée
Route::get('/debug-database', function () {
    $output = [];
    $output[] = "🔍 DEBUG DATABASE CONNECTION";
    $output[] = "=============================";
    
    try {
        // Configuration actuelle
        $output[] = "📊 Configuration Laravel:";
        $output[] = "Default connection: " . config('database.default');
        $output[] = "DB_CONNECTION env: " . env('DB_CONNECTION', 'NOT SET');
        $output[] = "DATABASE_URL env: " . (env('DATABASE_URL') ? 'SET' : 'NOT SET');
        
        // Test de connexion
        $output[] = "\n🔗 Test de connexion:";
        $connection = DB::connection();
        $driverName = $connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $output[] = "Driver utilisé: " . $driverName;
        
        if ($driverName === 'pgsql') {
            $output[] = "✅ PostgreSQL connecté!";
        } else {
            $output[] = "❌ Autre base: " . $driverName;
        }
        
        // Test une requête simple
        $output[] = "\n📝 Test requête:";
        $dbName = DB::select('SELECT current_database() as db')[0]->db ?? 'UNKNOWN';
        $output[] = "Base actuelle: " . $dbName;
        
        // Vérifier les utilisateurs
        $output[] = "\n👥 Test table users:";
        try {
            $userCount = \App\Models\User::count();
            $output[] = "✅ Table users accessible - {$userCount} utilisateurs";
            
            // Lister quelques utilisateurs
            $users = \App\Models\User::select('email', 'created_at')->limit(3)->get();
            foreach ($users as $user) {
                $output[] = "  - {$user->email} (créé: {$user->created_at})";
            }
            
        } catch (\Exception $e) {
            $output[] = "❌ Erreur table users: " . $e->getMessage();
        }
        
        // Variables d'environnement
        $output[] = "\n🌍 Variables d'environnement:";
        $output[] = "\$_ENV['DB_CONNECTION']: " . ($_ENV['DB_CONNECTION'] ?? 'NOT SET');
        $output[] = "\$_ENV['DATABASE_URL']: " . (isset($_ENV['DATABASE_URL']) ? 'SET' : 'NOT SET');
        $output[] = "getenv('DB_CONNECTION'): " . (getenv('DB_CONNECTION') ?: 'NOT SET');
        
    } catch (\Exception $e) {
        $output[] = "❌ Erreur: " . $e->getMessage();
        $output[] = "Stack trace: " . $e->getTraceAsString();
    }
    
    return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
           implode("\n", $output) . 
           '</pre><br>' .
           '<a href="/login" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔑 Test Login</a> ' .
           '<a href="/init-production" style="background: #32cd32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🚀 Re-Init</a>';
});

// 🔧 ROUTE POUR LANCER LA MIGRATION TAG
Route::get('/migrate-tag', function () {
    $output = [];
    $output[] = "🔧 MIGRATION TAG - Add tag column to users";
    $output[] = "==========================================";
    
    try {
        // Force PostgreSQL configuration
        putenv('DB_CONNECTION=pgsql');
        putenv('DATABASE_URL=postgresql://postgres:ARONEEXyEVmyfNShkXDBAyDSoFswjSAv@postgres.railway.internal:5432/railway');
        config(['database.default' => 'pgsql']);
        
        $output[] = "🐘 PostgreSQL configuration forced";

        // Run specific migration
        $output[] = "📊 Running tag migration...";
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_07_150000_add_tag_to_users_table.php', '--force' => true]);
        $migrationOutput = \Illuminate\Support\Facades\Artisan::output();
        $output[] = $migrationOutput;
        $output[] = "✅ Tag migration completed";

        // Test tag field
        $output[] = "\n🏷️ Testing tag functionality...";
        $user = \App\Models\User::first();
        if ($user) {
            $user->tag = '@test' . time();
            $user->save();
            $output[] = "✅ Tag field working: " . $user->tag;
        }

        $output[] = "\n🎉 PROFILE UPDATE READY!";
        $output[] = "========================";
        $output[] = "✅ Tag field added to users table";
        $output[] = "✅ Profile edit form will now work";
        $output[] = "✅ You can now update your name and tag";

    } catch (Exception $e) {
        $output[] = "❌ Error: " . $e->getMessage();
        $output[] = "Stack trace: " . $e->getTraceAsString();
    }
    
    return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
           implode("\n", $output) . 
           '</pre><br>' .
           '<a href="/profile/edit" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👤 Test Profile</a> ' .
           '<a href="/login" style="background: #32cd32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔑 Login</a>';
});

// 🔍 ROUTE DEBUG ANALYSE NUTRITIONNELLE
Route::get('/debug-nutrition/{postId?}', function ($postId = null) {
    $output = [];
    $output[] = "🔍 DEBUG ANALYSE NUTRITIONNELLE";
    $output[] = "==============================";
    
    try {
        // Force PostgreSQL
        putenv('DB_CONNECTION=pgsql');
        config(['database.default' => 'pgsql']);
        
        if ($postId) {
            $post = \App\Models\Post::find($postId);
            if (!$post) {
                $output[] = "❌ Post #$postId non trouvé";
                return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace;">' . implode("\n", $output) . '</pre>';
            }
        } else {
            // Prendre le dernier post
            $post = \App\Models\Post::latest()->first();
            if (!$post) {
                $output[] = "❌ Aucun post trouvé";
                return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace;">' . implode("\n", $output) . '</pre>';
            }
        }
        
        $output[] = "📝 Post #" . $post->id . " par " . $post->user->name;
        $output[] = "📅 Créé: " . $post->created_at;
        $output[] = "🖼️ Image URL: " . $post->image_path;
        $output[] = "📊 Analyse existante: " . ($post->nutrition_analysis ? 'OUI' : 'NON');
        
        if ($post->nutrition_analysis) {
            $output[] = "\n✅ ANALYSE EXISTANTE:";
            $output[] = "---";
            $output[] = substr($post->nutrition_analysis, 0, 200) . "...";
            $output[] = "---";
        }
        
        // Test de l'URL d'image
        $output[] = "\n🔗 Test accessibilité image:";
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($post->image_path);
            $output[] = "Status: " . $response->status();
            $output[] = "Content-Type: " . $response->header('Content-Type');
            $output[] = "Taille: " . strlen($response->body()) . " bytes";
        } catch (\Exception $e) {
            $output[] = "❌ Erreur accès image: " . $e->getMessage();
        }
        
        // Test API OpenAI
        $output[] = "\n🤖 Test API OpenAI:";
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            $output[] = "❌ Clé API OpenAI manquante";
        } else {
            $output[] = "✅ Clé API configurée: " . substr($apiKey, 0, 8) . "...";
            
            // Re-déclencher l'analyse si demandé
            if (request('reanalyze') === '1') {
                $output[] = "\n🚀 RE-DÉCLENCHEMENT ANALYSE...";
                
                try {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => [
                                    [
                                        'type' => 'text', 
                                        'text' => 'Analyse cette image de repas et donne moi les informations nutritionnelles: calories, glucides, protéines, lipides et un score santé sur 10.'
                                    ],
                                    [
                                        'type' => 'image_url',
                                        'image_url' => ['url' => $post->image_path]
                                    ]
                                ]
                            ]
                        ],
                        'max_tokens' => 500,
                        'temperature' => 0.7
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $content = $data['choices'][0]['message']['content'] ?? 'Pas de contenu';
                        $post->nutrition_analysis = $content;
                        $post->save();
                        $output[] = "✅ Analyse mise à jour!";
                        $output[] = "Nouveau contenu: " . substr($content, 0, 200) . "...";
                    } else {
                        $output[] = "❌ Erreur OpenAI: " . $response->status();
                        $output[] = $response->body();
                    }
                } catch (\Exception $e) {
                    $output[] = "❌ Exception: " . $e->getMessage();
                }
            }
        }
        
    } catch (\Exception $e) {
        $output[] = "❌ Erreur: " . $e->getMessage();
    }
    
    return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
           implode("\n", $output) . 
           '</pre><br>' .
           '<a href="/debug-nutrition/' . ($post->id ?? '') . '?reanalyze=1" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🚀 Re-analyser</a> ' .
           '<a href="/social" style="background: #32cd32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🍽️ Retour Feed</a>';
});

// Route proxy pour servir les images R2 qui ne sont pas publiquement accessibles
Route::get('/r2-image/{path}', function ($path) {
    try {
        $r2Service = new \App\Services\CloudflareR2Service();
        
        // Décoder le chemin qui pourrait être encodé
        $decodedPath = urldecode($path);
        
        // Vérifier si le fichier existe
        if (!$r2Service->exists($decodedPath)) {
            abort(404, 'Image not found');
        }
        
        // Obtenir le contenu de l'image
        $imageContent = $r2Service->getContent($decodedPath);
        
        // Déterminer le type MIME basé sur l'extension
        $extension = strtolower(pathinfo($decodedPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';
        
        return response($imageContent)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000') // Cache 1 an
            ->header('Content-Length', strlen($imageContent));
            
    } catch (\Exception $e) {
        \Log::error('Erreur proxy R2 image: ' . $e->getMessage());
        abort(404, 'Image not accessible');
    }
})->where('path', '.*');

// 🔑 ROUTE POUR CONFIGURER OPENAI API ET RELANCER ANALYSE
Route::get('/fix-openai-api', function () {
    $output = [];
    $output[] = "🔑 CONFIGURATION OPENAI API + ANALYSE";
    $output[] = "=====================================";
    
    try {
        // Force PostgreSQL
        putenv('DB_CONNECTION=pgsql');
        config(['database.default' => 'pgsql']);
        
        // Configuration de la clé API OpenAI depuis l'environnement
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            $output[] = "❌ OPENAI_API_KEY non configurée";
            echo json_encode(['success' => false, 'error' => 'OPENAI_API_KEY manquante', 'output' => $output]);
            return;
        }
        $output[] = "✅ Clé API OpenAI configurée";
        
        // Trouver le dernier post sans analyse
        $post = \App\Models\Post::whereNull('nutrition_analysis')->orWhere('nutrition_analysis', '')->latest()->first();
        if (!$post) {
            $post = \App\Models\Post::latest()->first();
        }
        
        if (!$post) {
            $output[] = "❌ Aucun post trouvé";
            return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace;">' . implode("\n", $output) . '</pre>';
        }
        
        $output[] = "📝 Post trouvé: #" . $post->id;
        $output[] = "🖼️ URL image: " . $post->image_path;
        
        // Test accessibilité image
        $output[] = "\n🔗 Test accès image R2...";
        try {
            $imageResponse = \Illuminate\Support\Facades\Http::timeout(15)->get($post->image_path);
            $output[] = "✅ Image accessible - Status: " . $imageResponse->status();
            $output[] = "✅ Content-Type: " . $imageResponse->header('Content-Type');
            $output[] = "✅ Taille: " . number_format(strlen($imageResponse->body()) / 1024, 1) . " KB";
        } catch (\Exception $e) {
            $output[] = "❌ Erreur accès image: " . $e->getMessage();
        }
        
                         // Lancer l'analyse OpenAI
        $output[] = "\n🤖 LANCEMENT ANALYSE OPENAI...";
        
        // Télécharger l'image depuis R2 via le service CloudflareR2Service
        $output[] = "📥 Téléchargement image depuis R2 (via service)...";
        try {
            // Extraire le chemin relatif depuis l'URL R2
            $r2Service = new \App\Services\CloudflareR2Service();
            
            // L'URL est du format: https://0650ad87c8bc19f6312144dc1f66f405.r2.cloudflarestorage.com/zyma-files/posts/filename.jpg
            // On veut extraire: posts/filename.jpg
            $parsedUrl = parse_url($post->image_path);
            $path = ltrim($parsedUrl['path'], '/');
            
            // Supprimer le préfixe "zyma-files/" si présent
            if (str_starts_with($path, 'zyma-files/')) {
                $path = substr($path, strlen('zyma-files/'));
            }
            
            $output[] = "🔍 Chemin extrait: " . $path;
            
            // Télécharger via le service R2 avec credentials
            $imageContent = $r2Service->getContent($path);
            
            if ($imageContent) {
                $imageData = base64_encode($imageContent);
                $output[] = "✅ Image téléchargée via R2 Service (" . number_format(strlen($imageData)/1024, 1) . " KB en base64)";
            } else {
                throw new \Exception("Contenu image vide");
            }
        } catch (\Exception $e) {
            $output[] = "❌ Erreur téléchargement R2: " . $e->getMessage();
            return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace;">' . implode("\n", $output) . '</pre>';
        }
         
         $prompt = "Analyse cette image de repas et donne-moi les informations nutritionnelles suivantes :

**🍽️ Repas identifié** : [nom du plat]

**🔥 Calories** : [nombre] kcal  
**🥖 Glucides** : [nombre] g  
**🍗 Protéines** : [nombre] g  
**🥑 Lipides** : [nombre] g  

**💚 Score Santé Zyma** : [note]/10

✍️ **Feedback** :
[Conseils personnalisés en 2-3 lignes]";
         
         $response = \Illuminate\Support\Facades\Http::withHeaders([
             'Authorization' => 'Bearer ' . $apiKey,
             'Content-Type' => 'application/json',
         ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
             'model' => 'gpt-4o',
             'messages' => [
                 [
                     'role' => 'user',
                     'content' => [
                         ['type' => 'text', 'text' => $prompt],
                         [
                             'type' => 'image_url',
                             'image_url' => [
                                 'url' => 'data:image/jpeg;base64,' . $imageData
                             ]
                         ]
                     ]
                 ]
             ],
             'max_tokens' => 800,
             'temperature' => 0.7
         ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            
            if (!empty($content)) {
                $post->nutrition_analysis = $content;
                $post->save();
                
                $output[] = "✅ ANALYSE TERMINÉE ET SAUVEGARDÉE!";
                $output[] = "\n📊 RÉSULTAT:";
                $output[] = "---";
                $output[] = substr($content, 0, 300) . "...";
                $output[] = "---";
                
                $output[] = "\n🎉 SUCCÈS COMPLET!";
                $output[] = "✅ Clé API configurée";
                $output[] = "✅ Image R2 accessible";
                $output[] = "✅ Analyse OpenAI terminée";
                $output[] = "✅ Post mis à jour";
            } else {
                $output[] = "❌ Réponse OpenAI vide";
            }
        } else {
            $output[] = "❌ Erreur OpenAI: " . $response->status();
            $output[] = "❌ Message: " . $response->body();
        }
        
    } catch (\Exception $e) {
        $output[] = "❌ Exception: " . $e->getMessage();
        $output[] = "Stack: " . $e->getTraceAsString();
    }
    
    return '<pre style="background: #000; color: #0f0; padding: 20px; font-family: monospace; white-space: pre-wrap;">' . 
           implode("\n", $output) . 
           '</pre><br>' .
           '<a href="/social" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🍽️ Voir le Feed</a> ' .
           '<a href="/fix-openai-api" style="background: #32cd32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔄 Re-essayer</a>';
});