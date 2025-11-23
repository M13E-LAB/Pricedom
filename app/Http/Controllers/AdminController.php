<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use App\Models\Contribution;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware sera géré dans les routes ou directement dans les méthodes
    }

    public function dashboard()
    {
        $stats = [
            'total_contributions' => Contribution::count(),
            'pending_contributions' => Contribution::where('verified', false)->count(),
            'verified_contributions' => Contribution::where('verified', true)->count(),
            'total_users' => User::count(),
            'total_revenue' => Contribution::sum('price'),
            'scan_contributions' => Contribution::where('contribution_type', 'scan')->count(),
            'manual_contributions' => Contribution::where('contribution_type', 'manual')->count(),
        ];

        // Contributions récentes
        $recentContributions = Contribution::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Statistiques par magasin
        $storeStats = Contribution::select('store_name', 
            DB::raw('count(*) as total_contributions'),
            DB::raw('sum(price) as total_amount'))
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderByDesc('total_contributions')
            ->limit(10)
            ->get();

        // Statistiques par catégorie
        $categoryStats = Contribution::select('category',
            DB::raw('count(*) as total_contributions'),
            DB::raw('avg(price) as avg_price'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total_contributions')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentContributions', 'storeStats', 'categoryStats'));
    }

    public function contributions(Request $request)
    {
        $query = Contribution::with('user');

        // Filtres
        if ($request->has('verified') && $request->verified !== 'all') {
            $query->where('verified', $request->verified === 'true');
        }

        if ($request->has('contribution_type') && $request->contribution_type !== 'all') {
            $query->where('contribution_type', $request->contribution_type);
        }

        if ($request->has('store_name') && !empty($request->store_name)) {
            $query->where('store_name', 'like', '%' . $request->store_name . '%');
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $contributions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Options pour les filtres
        $stores = Contribution::whereNotNull('store_name')
            ->distinct()
            ->pluck('store_name')
            ->sort();

        $categories = Contribution::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort();

        return view('admin.contributions', compact('contributions', 'stores', 'categories'));
    }

    public function verifyContribution(Request $request, $id)
    {
        $contribution = Contribution::findOrFail($id);
        $contribution->verified = $request->verified === 'true';
        $contribution->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut de vérification mis à jour'
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $query = Contribution::with('user');

            // Appliquer les mêmes filtres que la vue
            if ($request->has('verified') && $request->verified !== 'all') {
                $query->where('verified', $request->verified === 'true');
            }

            if ($request->has('contribution_type') && $request->contribution_type !== 'all') {
                $query->where('contribution_type', $request->contribution_type);
            }

            if ($request->has('store_name') && !empty($request->store_name)) {
                $query->where('store_name', 'like', '%' . $request->store_name . '%');
            }

            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $contributions = $query->orderBy('created_at', 'desc')->get();

            // Créer le fichier Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Titre
            $sheet->setCellValue('A1', 'ZYMA - Export des Contributions');
            $sheet->mergeCells('A1:L1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4CAF50');

            // Date d'export
            $sheet->setCellValue('A2', 'Export généré le : ' . now()->format('d/m/Y H:i'));
            $sheet->mergeCells('A2:L2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // En-têtes
            $headers = [
                'ID', 'Utilisateur', 'Email', 'Produit', 'Prix (€)', 'Quantité', 
                'Magasin', 'Lieu', 'Catégorie', 'Type', 'Vérifié', 'Date'
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '4', $header);
                $sheet->getStyle($col . '4')->getFont()->setBold(true);
                $sheet->getStyle($col . '4')->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                $sheet->getStyle($col . '4')->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }

            // Données
            $row = 5;
            foreach ($contributions as $contribution) {
                $sheet->setCellValue('A' . $row, $contribution->id);
                $sheet->setCellValue('B' . $row, $contribution->user->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $contribution->user->email ?? 'N/A');
                $sheet->setCellValue('D' . $row, $contribution->product_name);
                $sheet->setCellValue('E' . $row, number_format($contribution->price, 2));
                $sheet->setCellValue('F' . $row, $contribution->quantity ?? 1);
                $sheet->setCellValue('G' . $row, $contribution->store_name ?? 'N/A');
                $sheet->setCellValue('H' . $row, $contribution->location ?? 'N/A');
                $sheet->setCellValue('I' . $row, $contribution->category ?? 'N/A');
                $sheet->setCellValue('J' . $row, ucfirst($contribution->contribution_type));
                $sheet->setCellValue('K' . $row, $contribution->verified ? 'Oui' : 'Non');
                $sheet->setCellValue('L' . $row, $contribution->created_at->format('d/m/Y H:i'));

                // Style alternatif pour les lignes
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':L' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8F9FA');
                }

                // Colorier selon la vérification
                if ($contribution->verified) {
                    $sheet->getStyle('K' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFD4EDDA');
                } else {
                    $sheet->getStyle('K' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8D7DA');
                }

                $row++;
            }

            // Ajuster la largeur des colonnes
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Statistiques en bas
            $row += 2;
            $sheet->setCellValue('A' . $row, 'STATISTIQUES');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            
            $row++;
            $sheet->setCellValue('A' . $row, 'Total contributions:');
            $sheet->setCellValue('B' . $row, $contributions->count());
            
            $row++;
            $sheet->setCellValue('A' . $row, 'Total vérifié:');
            $sheet->setCellValue('B' . $row, $contributions->where('verified', true)->count());
            
            $row++;
            $sheet->setCellValue('A' . $row, 'Montant total:');
            $sheet->setCellValue('B' . $row, number_format($contributions->sum('price'), 2) . ' €');

            // Générer le fichier
            $filename = 'contributions_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            
            // Headers pour le téléchargement
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            \Log::error('Erreur export Excel: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function analytics()
    {
        // Vérifier que l'utilisateur est admin
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        // Calculer les statistiques
        $stats = $this->calculateStats();
        $recent_users = $this->getRecentUsers();

        return view('admin.analytics', compact('stats', 'recent_users'));
    }

    private function calculateStats()
    {
        $now = Carbon::now();
        $weekAgo = $now->copy()->subWeek();
        $yesterday = $now->copy()->subDay();

        // Statistiques principales
        $stats = [
            'total_users' => User::count(),
            'new_users_week' => User::where('created_at', '>=', $weekAgo)->count(),
            'active_today' => User::where('updated_at', '>=', $now->startOfDay())->count(),
            'active_yesterday' => User::where('updated_at', '>=', $yesterday->startOfDay())
                                    ->where('updated_at', '<', $now->startOfDay())->count(),
            'total_posts' => 0, // À adapter selon votre modèle de posts
            'posts_week' => 0,
            'total_searches' => 0, // À adapter selon votre système de tracking
            'searches_today' => 0,
            
            // ✨ NOUVELLES STATISTIQUES DE PRIX
            'total_contributions' => Contribution::count(),
            'total_revenue' => Contribution::sum('price'),
            'average_price' => Contribution::avg('price'),
            'highest_price' => Contribution::max('price'),
            'contributions_today' => Contribution::whereDate('created_at', $now->toDateString())->count(),
            'revenue_today' => Contribution::whereDate('created_at', $now->toDateString())->sum('price'),
            'contributions_week' => Contribution::where('created_at', '>=', $weekAgo)->count(),
            'revenue_week' => Contribution::where('created_at', '>=', $weekAgo)->sum('price'),
            'verified_contributions' => Contribution::where('verified', true)->count(),
            'pending_contributions' => Contribution::where('verified', false)->count(),
        ];

        // Activité des 7 derniers jours
        $dailyActivity = [];
        $dailyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $day = $date->format('D');
            $count = User::where('updated_at', '>=', $date->startOfDay())
                        ->where('updated_at', '<', $date->endOfDay())
                        ->count();
            $revenue = Contribution::whereDate('created_at', $date->toDateString())->sum('price');
            $dailyActivity[$day] = $count;
            $dailyRevenue[$day] = $revenue;
        }
        $stats['daily_activity'] = $dailyActivity;
        $stats['daily_revenue'] = $dailyRevenue;

        // Top 5 des magasins par revenus
        $stats['top_stores'] = Contribution::select('store_name', 
            DB::raw('COUNT(*) as total_contributions'),
            DB::raw('SUM(price) as total_revenue'))
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Top 5 des catégories par prix moyen
        $stats['top_categories'] = Contribution::select('category',
            DB::raw('COUNT(*) as total_contributions'),
            DB::raw('AVG(price) as avg_price'),
            DB::raw('SUM(price) as total_revenue'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('avg_price')
            ->limit(5)
            ->get();

        // Actions populaires avec nouvelles données
        $stats['top_actions'] = [
            [
                'icon' => '👤',
                'name' => 'Inscriptions',
                'description' => 'Nouveaux comptes créés',
                'count' => $stats['new_users_week']
            ],
            [
                'icon' => '🛒',
                'name' => 'Contributions',
                'description' => 'Produits ajoutés cette semaine',
                'count' => $stats['contributions_week']
            ],
            [
                'icon' => '💰',
                'name' => 'Revenus',
                'description' => 'Revenus cette semaine',
                'count' => number_format($stats['revenue_week'], 2) . ' €'
            ],
            [
                'icon' => '🟢',
                'name' => 'Connexions',
                'description' => 'Utilisateurs actifs',
                'count' => $stats['active_today']
            ]
        ];

        return $stats;
    }

    private function getRecentUsers($limit = 10)
    {
        return User::orderBy('created_at', 'desc')
                  ->take($limit)
                  ->get();
    }

    // Export des utilisateurs
    public function exportUsers()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        $users = User::all();
        
        $csvData = "ID,Nom,Email,Date d'inscription,Dernière activité,Tag\n";
        
        foreach ($users as $user) {
            $csvData .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $user->id,
                str_replace('"', '""', $user->name),
                $user->email,
                $user->created_at->format('Y-m-d H:i:s'),
                $user->updated_at->format('Y-m-d H:i:s'),
                $user->tag ?? ''
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="utilisateurs_' . date('Y-m-d') . '.csv"');
    }

    // Export des posts
    public function exportPosts()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        // À adapter selon votre modèle de posts
        $csvData = "ID,Utilisateur,Contenu,Date de création\n";
        $csvData .= "Aucun post trouvé\n"; // Placeholder

        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="posts_' . date('Y-m-d') . '.csv"');
    }

    // Export de l'activité
    public function exportActivity()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        $users = User::all();
        
        $csvData = "Utilisateur,Email,Première connexion,Dernière activité,Nombre de jours depuis inscription\n";
        
        foreach ($users as $user) {
            $daysSinceRegistration = $user->created_at->diffInDays(Carbon::now());
            $csvData .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",%d\n",
                str_replace('"', '""', $user->name),
                $user->email,
                $user->created_at->format('Y-m-d H:i:s'),
                $user->updated_at->format('Y-m-d H:i:s'),
                $daysSinceRegistration
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="activite_' . date('Y-m-d') . '.csv"');
    }

    // Export du rapport complet
    public function exportFull()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        $stats = $this->calculateStats();
        $users = User::all();
        
        $csvData = "=== RAPPORT COMPLET ZYMA - " . date('Y-m-d H:i:s') . " ===\n\n";
        
        // Statistiques générales
        $csvData .= "STATISTIQUES GÉNÉRALES\n";
        $csvData .= "Utilisateurs total," . $stats['total_users'] . "\n";
        $csvData .= "Nouveaux utilisateurs (7 jours)," . $stats['new_users_week'] . "\n";
        $csvData .= "Utilisateurs actifs aujourd'hui," . $stats['active_today'] . "\n";
        $csvData .= "Posts total," . $stats['total_posts'] . "\n";
        $csvData .= "Recherches total," . $stats['total_searches'] . "\n";
        $csvData .= "\n";
        
        // Activité quotidienne
        $csvData .= "ACTIVITÉ DES 7 DERNIERS JOURS\n";
        foreach ($stats['daily_activity'] as $day => $count) {
            $csvData .= "$day,$count\n";
        }
        $csvData .= "\n";
        
        // Liste des utilisateurs
        $csvData .= "LISTE COMPLÈTE DES UTILISATEURS\n";
        $csvData .= "ID,Nom,Email,Date d'inscription,Dernière activité,Tag\n";
        
        foreach ($users as $user) {
            $csvData .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $user->id,
                str_replace('"', '""', $user->name),
                $user->email,
                $user->created_at->format('Y-m-d H:i:s'),
                $user->updated_at->format('Y-m-d H:i:s'),
                $user->tag ?? ''
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="rapport_complet_zyma_' . date('Y-m-d') . '.csv"');
    }

    // 🆕 NOUVEAU DASHBOARD PRIX
    public function pricesDashboard()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        $now = Carbon::now();
        $weekAgo = $now->copy()->subWeek();
        $monthAgo = $now->copy()->subMonth();

        // Statistiques spécifiques aux prix
        $priceStats = [
            'total_contributions' => Contribution::count(),
            'total_revenue' => Contribution::sum('price'),
            'average_price' => Contribution::avg('price'),
            'highest_price' => Contribution::max('price'),
            'lowest_price' => Contribution::min('price'),
            'median_price' => Contribution::orderBy('price')->skip(floor(Contribution::count() / 2))->first()->price ?? 0,
            
            // Par période
            'revenue_today' => Contribution::whereDate('created_at', $now->toDateString())->sum('price'),
            'revenue_week' => Contribution::where('created_at', '>=', $weekAgo)->sum('price'),
            'revenue_month' => Contribution::where('created_at', '>=', $monthAgo)->sum('price'),
            
            'contributions_today' => Contribution::whereDate('created_at', $now->toDateString())->count(),
            'contributions_week' => Contribution::where('created_at', '>=', $weekAgo)->count(),
            'contributions_month' => Contribution::where('created_at', '>=', $monthAgo)->count(),
            
            // Par statut
            'verified_contributions' => Contribution::where('verified', true)->count(),
            'pending_contributions' => Contribution::where('verified', false)->count(),
            'verified_revenue' => Contribution::where('verified', true)->sum('price'),
            'pending_revenue' => Contribution::where('verified', false)->sum('price'),
        ];

        // Revenus des 30 derniers jours
        $dailyRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $revenue = Contribution::whereDate('created_at', $date->toDateString())->sum('price');
            $dailyRevenue[$date->format('M d')] = $revenue;
        }
        $priceStats['daily_revenue'] = $dailyRevenue;

        // Top magasins par revenus
        $topStores = Contribution::select('store_name', 
            DB::raw('COUNT(*) as total_contributions'),
            DB::raw('SUM(price) as total_revenue'),
            DB::raw('AVG(price) as avg_price'))
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // Top catégories par prix moyen
        $topCategories = Contribution::select('category',
            DB::raw('COUNT(*) as total_contributions'),
            DB::raw('AVG(price) as avg_price'),
            DB::raw('SUM(price) as total_revenue'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('avg_price')
            ->limit(10)
            ->get();

        // Distributions de prix
        $priceRanges = [
            '0-1€' => Contribution::whereBetween('price', [0, 1])->count(),
            '1-5€' => Contribution::whereBetween('price', [1.01, 5])->count(),
            '5-10€' => Contribution::whereBetween('price', [5.01, 10])->count(),
            '10-20€' => Contribution::whereBetween('price', [10.01, 20])->count(),
            '20-50€' => Contribution::whereBetween('price', [20.01, 50])->count(),
            '50€+' => Contribution::where('price', '>', 50)->count(),
        ];

        return view('admin.prices', compact('priceStats', 'topStores', 'topCategories', 'priceRanges'));
    }

    // 🆕 NOUVEAU DASHBOARD UTILISATEURS
    public function usersDashboard()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        $now = Carbon::now();
        $weekAgo = $now->copy()->subWeek();
        $monthAgo = $now->copy()->subMonth();

        // Statistiques utilisateurs
        $userStats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', $now->toDateString())->count(),
            'new_users_week' => User::where('created_at', '>=', $weekAgo)->count(),
            'new_users_month' => User::where('created_at', '>=', $monthAgo)->count(),
            
            'active_today' => User::where('updated_at', '>=', $now->startOfDay())->count(),
            'active_week' => User::where('updated_at', '>=', $weekAgo)->count(),
            'active_month' => User::where('updated_at', '>=', $monthAgo)->count(),
        ];

        // Inscriptions des 30 derniers jours
        $dailyRegistrations = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $count = User::whereDate('created_at', $date->toDateString())->count();
            $dailyRegistrations[$date->format('M d')] = $count;
        }
        $userStats['daily_registrations'] = $dailyRegistrations;

        // Utilisateurs les plus actifs (par contributions)
        $topContributors = User::select('users.*', 
            DB::raw('COUNT(contributions.id) as contributions_count'),
            DB::raw('SUM(contributions.price) as total_contributed'))
            ->leftJoin('contributions', 'users.id', '=', 'contributions.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at', 'users.updated_at', 'users.email_verified_at', 'users.password', 'users.remember_token', 'users.tag')
            ->orderByDesc('contributions_count')
            ->limit(10)
            ->get();

        // Utilisateurs récents avec détails
        $recentUsers = User::with(['contributions' => function($query) {
                $query->select('user_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(price) as total'))
                      ->groupBy('user_id');
            }])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.users', compact('userStats', 'topContributors', 'recentUsers'));
    }

    // 🆕 EXPORT SPÉCIALISÉ PRIX
    public function exportPrices()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        try {
            $contributions = Contribution::with('user')->orderBy('created_at', 'desc')->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Titre
            $sheet->setCellValue('A1', 'ZYMA - Export Détaillé des Prix');
            $sheet->mergeCells('A1:M1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2E7D32');

            // Statistiques en haut
            $sheet->setCellValue('A3', 'STATISTIQUES GÉNÉRALES');
            $sheet->getStyle('A3')->getFont()->setBold(true);
            
            $sheet->setCellValue('A4', 'Total contributions:');
            $sheet->setCellValue('B4', $contributions->count());
            $sheet->setCellValue('A5', 'Revenus total:');
            $sheet->setCellValue('B5', number_format($contributions->sum('price'), 2) . ' €');
            $sheet->setCellValue('A6', 'Prix moyen:');
            $sheet->setCellValue('B6', number_format($contributions->avg('price'), 2) . ' €');
            $sheet->setCellValue('A7', 'Prix maximum:');
            $sheet->setCellValue('B7', number_format($contributions->max('price'), 2) . ' €');

            // En-têtes détaillés
            $headers = [
                'ID', 'Produit', 'Prix (€)', 'Quantité', 'Prix Total', 'Magasin', 'Lieu', 'Catégorie', 
                'Utilisateur', 'Email', 'Type', 'Vérifié', 'Date'
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '9', $header);
                $sheet->getStyle($col . '9')->getFont()->setBold(true);
                $sheet->getStyle($col . '9')->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEEEEEE');
                $col++;
            }

            // Données
            $row = 10;
            foreach ($contributions as $contribution) {
                $totalPrice = $contribution->price * ($contribution->quantity ?? 1);
                
                $sheet->setCellValue('A' . $row, $contribution->id);
                $sheet->setCellValue('B' . $row, $contribution->product_name);
                $sheet->setCellValue('C' . $row, number_format($contribution->price, 2));
                $sheet->setCellValue('D' . $row, $contribution->quantity ?? 1);
                $sheet->setCellValue('E' . $row, number_format($totalPrice, 2));
                $sheet->setCellValue('F' . $row, $contribution->store_name ?? 'N/A');
                $sheet->setCellValue('G' . $row, $contribution->location ?? 'N/A');
                $sheet->setCellValue('H' . $row, $contribution->category ?? 'N/A');
                $sheet->setCellValue('I' . $row, $contribution->user->name ?? 'N/A');
                $sheet->setCellValue('J' . $row, $contribution->user->email ?? 'N/A');
                $sheet->setCellValue('K' . $row, ucfirst($contribution->contribution_type));
                $sheet->setCellValue('L' . $row, $contribution->verified ? 'Oui' : 'Non');
                $sheet->setCellValue('M' . $row, $contribution->created_at->format('d/m/Y H:i'));

                // Couleur selon vérification
                if ($contribution->verified) {
                    $sheet->getStyle('L' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFD4EDDA');
                } else {
                    $sheet->getStyle('L' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8D7DA');
                }

                $row++;
            }

            // Ajuster colonnes
            foreach (range('A', 'M') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'prix_detailles_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            \Log::error('Erreur export prix: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'export des prix');
        }
    }

    // 🆕 EXPORT SPÉCIALISÉ UTILISATEURS
    public function exportUsersData()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        try {
            $users = User::with(['contributions'])->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Titre
            $sheet->setCellValue('A1', 'ZYMA - Export Détaillé des Utilisateurs');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1976D2');

            // En-têtes
            $headers = [
                'ID', 'Nom', 'Email', 'Date inscription', 'Dernière activité', 
                'Contributions', 'Revenus générés (€)', 'Contributions vérifiées', 'Tag', 'Statut'
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '3', $header);
                $sheet->getStyle($col . '3')->getFont()->setBold(true);
                $sheet->getStyle($col . '3')->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEEEEEE');
                $col++;
            }

            // Données
            $row = 4;
            foreach ($users as $user) {
                $contributionsCount = $user->contributions->count();
                $totalRevenue = $user->contributions->sum('price');
                $verifiedCount = $user->contributions->where('verified', true)->count();
                $daysSinceLastActivity = $user->updated_at->diffInDays(now());
                
                $status = 'Inactif';
                if ($daysSinceLastActivity == 0) $status = 'Actif aujourd\'hui';
                elseif ($daysSinceLastActivity <= 7) $status = 'Actif cette semaine';
                elseif ($daysSinceLastActivity <= 30) $status = 'Actif ce mois';

                $sheet->setCellValue('A' . $row, $user->id);
                $sheet->setCellValue('B' . $row, $user->name);
                $sheet->setCellValue('C' . $row, $user->email);
                $sheet->setCellValue('D' . $row, $user->created_at->format('d/m/Y H:i'));
                $sheet->setCellValue('E' . $row, $user->updated_at->format('d/m/Y H:i'));
                $sheet->setCellValue('F' . $row, $contributionsCount);
                $sheet->setCellValue('G' . $row, number_format($totalRevenue, 2));
                $sheet->setCellValue('H' . $row, $verifiedCount);
                $sheet->setCellValue('I' . $row, $user->tag ?? 'Aucun');
                $sheet->setCellValue('J' . $row, $status);

                // Couleur selon l'activité
                if ($daysSinceLastActivity == 0) {
                    $sheet->getStyle('J' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFD4EDDA');
                } elseif ($daysSinceLastActivity <= 7) {
                    $sheet->getStyle('J' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFFEAA7');
                }

                $row++;
            }

            // Ajuster colonnes
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'utilisateurs_detailles_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            \Log::error('Erreur export utilisateurs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'export des utilisateurs');
        }
    }

    // 🆕 EXPORT STATISTIQUES MAGASINS
    public function exportStoresStats()
    {
        if (!Auth::user() || Auth::user()->email !== 'maessakhi99@gmail.com') {
            abort(403, 'Accès refusé');
        }

        $storesStats = Contribution::select('store_name', 
            DB::raw('COUNT(*) as total_contributions'),
            DB::raw('SUM(price) as total_revenue'),
            DB::raw('AVG(price) as avg_price'),
            DB::raw('MIN(price) as min_price'),
            DB::raw('MAX(price) as max_price'),
            DB::raw('COUNT(DISTINCT user_id) as unique_users'))
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderByDesc('total_revenue')
            ->get();

        $csvData = "ZYMA - Statistiques par Magasin - " . now()->format('Y-m-d H:i:s') . "\n\n";
        $csvData .= "Magasin,Contributions,Revenus (€),Prix Moyen (€),Prix Min (€),Prix Max (€),Utilisateurs Uniques\n";
        
        foreach ($storesStats as $store) {
            $csvData .= sprintf(
                "\"%s\",%d,%.2f,%.2f,%.2f,%.2f,%d\n",
                str_replace('"', '""', $store->store_name),
                $store->total_contributions,
                $store->total_revenue,
                $store->avg_price,
                $store->min_price,
                $store->max_price,
                $store->unique_users
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="statistiques_magasins_' . date('Y-m-d') . '.csv"');
    }
} 