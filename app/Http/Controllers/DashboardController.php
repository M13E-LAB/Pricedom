<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques globales
        $totalContributions = Contribution::count();
        $totalUsers = Contribution::distinct('user_id')->count();
        $averagePrice = Contribution::avg('price');
        $totalValue = Contribution::sum(DB::raw('price * quantity'));
        
        // Contributions récentes (avec utilisateur)
        $recentContributions = Contribution::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Top produits par nombre de contributions
        $topProducts = Contribution::select('product_name', DB::raw('COUNT(*) as count'), DB::raw('AVG(price) as avg_price'))
            ->groupBy('product_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        // Top magasins
        $topStores = Contribution::select('store_name', DB::raw('COUNT(*) as count'))
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        // Contributions par catégorie
        $contributionsByCategory = Contribution::select('category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();
        
        // Contributions par type (scan vs manual)
        $contributionsByType = Contribution::select('contribution_type', DB::raw('COUNT(*) as count'))
            ->groupBy('contribution_type')
            ->get();
        
        // Contributions par jour (7 derniers jours)
        $contributionsByDay = Contribution::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return view('dashboard.index', compact(
            'totalContributions',
            'totalUsers',
            'averagePrice',
            'totalValue',
            'recentContributions',
            'topProducts',
            'topStores',
            'contributionsByCategory',
            'contributionsByType',
            'contributionsByDay'
        ));
    }

    public function exportExcel()
    {
        // Créer un nouveau spreadsheet
        $spreadsheet = new Spreadsheet();
        
        // ============ FEUILLE 1: STATISTIQUES GLOBALES ============
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Statistiques');
        
        // En-tête
        $sheet1->setCellValue('A1', '📊 DASHBOARD ZYMA - STATISTIQUES GLOBALES');
        $sheet1->mergeCells('A1:D1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FF6B35');
        
        // Date d'export
        $sheet1->setCellValue('A2', 'Exporté le: ' . now()->format('d/m/Y H:i'));
        $sheet1->mergeCells('A2:D2');
        
        // Statistiques
        $totalContributions = Contribution::count();
        $totalUsers = Contribution::distinct('user_id')->count();
        $averagePrice = Contribution::avg('price');
        $totalValue = Contribution::sum(DB::raw('price * quantity'));
        
        $sheet1->setCellValue('A4', 'Métrique');
        $sheet1->setCellValue('B4', 'Valeur');
        $sheet1->getStyle('A4:B4')->getFont()->setBold(true);
        $sheet1->getStyle('A4:B4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8E8E8');
        
        $sheet1->setCellValue('A5', '🎯 Total Contributions');
        $sheet1->setCellValue('B5', $totalContributions);
        
        $sheet1->setCellValue('A6', '👥 Utilisateurs Actifs');
        $sheet1->setCellValue('B6', $totalUsers);
        
        $sheet1->setCellValue('A7', '💰 Prix Moyen');
        $sheet1->setCellValue('B7', number_format($averagePrice, 2) . ' €');
        
        $sheet1->setCellValue('A8', '💎 Valeur Totale');
        $sheet1->setCellValue('B8', number_format($totalValue, 2) . ' €');
        
        // Largeur des colonnes
        $sheet1->getColumnDimension('A')->setWidth(30);
        $sheet1->getColumnDimension('B')->setWidth(20);
        
        // ============ FEUILLE 2: TOP PRODUITS ============
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Top Produits');
        
        $sheet2->setCellValue('A1', '🏆 TOP 10 PRODUITS');
        $sheet2->mergeCells('A1:D1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4CAF50');
        
        $sheet2->setCellValue('A3', 'Produit');
        $sheet2->setCellValue('B3', 'Contributions');
        $sheet2->setCellValue('C3', 'Prix Moyen');
        $sheet2->getStyle('A3:C3')->getFont()->setBold(true);
        $sheet2->getStyle('A3:C3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8E8E8');
        
        $topProducts = Contribution::select('product_name', DB::raw('COUNT(*) as count'), DB::raw('AVG(price) as avg_price'))
            ->groupBy('product_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $row = 4;
        foreach ($topProducts as $product) {
            $sheet2->setCellValue('A' . $row, $product->product_name);
            $sheet2->setCellValue('B' . $row, $product->count);
            $sheet2->setCellValue('C' . $row, number_format($product->avg_price, 2) . ' €');
            $row++;
        }
        
        $sheet2->getColumnDimension('A')->setWidth(40);
        $sheet2->getColumnDimension('B')->setWidth(15);
        $sheet2->getColumnDimension('C')->setWidth(15);
        
        // ============ FEUILLE 3: TOP MAGASINS ============
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Top Magasins');
        
        $sheet3->setCellValue('A1', '🏪 TOP MAGASINS');
        $sheet3->mergeCells('A1:C1');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet3->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet3->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2196F3');
        
        $sheet3->setCellValue('A3', 'Magasin');
        $sheet3->setCellValue('B3', 'Contributions');
        $sheet3->getStyle('A3:B3')->getFont()->setBold(true);
        $sheet3->getStyle('A3:B3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8E8E8');
        
        $topStores = Contribution::select('store_name', DB::raw('COUNT(*) as count'))
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $row = 4;
        foreach ($topStores as $store) {
            $sheet3->setCellValue('A' . $row, $store->store_name);
            $sheet3->setCellValue('B' . $row, $store->count);
            $row++;
        }
        
        $sheet3->getColumnDimension('A')->setWidth(30);
        $sheet3->getColumnDimension('B')->setWidth(15);
        
        // ============ FEUILLE 4: CONTRIBUTIONS RÉCENTES ============
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Contributions Récentes');
        
        $sheet4->setCellValue('A1', '🕐 CONTRIBUTIONS RÉCENTES');
        $sheet4->mergeCells('A1:F1');
        $sheet4->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet4->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet4->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('9C27B0');
        
        $sheet4->setCellValue('A3', 'Produit');
        $sheet4->setCellValue('B3', 'Prix');
        $sheet4->setCellValue('C3', 'Quantité');
        $sheet4->setCellValue('D3', 'Magasin');
        $sheet4->setCellValue('E3', 'Utilisateur');
        $sheet4->setCellValue('F3', 'Date');
        $sheet4->setCellValue('G3', 'Type');
        $sheet4->getStyle('A3:G3')->getFont()->setBold(true);
        $sheet4->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8E8E8');
        
        $recentContributions = Contribution::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        $row = 4;
        foreach ($recentContributions as $contribution) {
            $sheet4->setCellValue('A' . $row, $contribution->product_name);
            $sheet4->setCellValue('B' . $row, number_format($contribution->price, 2) . ' €');
            $sheet4->setCellValue('C' . $row, $contribution->quantity ?? 1);
            $sheet4->setCellValue('D' . $row, $contribution->store_name ?? 'N/A');
            $sheet4->setCellValue('E' . $row, $contribution->user->name);
            $sheet4->setCellValue('F' . $row, $contribution->created_at->format('d/m/Y H:i'));
            $sheet4->setCellValue('G' . $row, $contribution->contribution_type === 'scan' ? '📷 Scan' : '✏️ Manuel');
            $row++;
        }
        
        $sheet4->getColumnDimension('A')->setWidth(35);
        $sheet4->getColumnDimension('B')->setWidth(12);
        $sheet4->getColumnDimension('C')->setWidth(10);
        $sheet4->getColumnDimension('D')->setWidth(20);
        $sheet4->getColumnDimension('E')->setWidth(20);
        $sheet4->getColumnDimension('F')->setWidth(18);
        $sheet4->getColumnDimension('G')->setWidth(12);
        
        // Générer le fichier Excel
        $filename = 'dashboard_zyma_' . now()->format('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        // Headers pour le téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}


