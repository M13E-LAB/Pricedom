<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;

class LeagueController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer le classement des utilisateurs
        $rankings = $this->getHealthyRankings();
        
        // Position de l'utilisateur actuel
        $userRank = $this->getUserRank($user->id);
        
        // Statistiques globales
        $stats = [
            'total_players' => User::count(),
            'total_healthy_posts' => Post::where('health_score', '>', 70)->count(),
            'average_health_score' => Post::avg('health_score') ?? 0,
            'top_scorer_this_week' => $this->getTopScorerThisWeek()
        ];
        
        return view('league.index', compact('rankings', 'userRank', 'stats', 'user'));
    }
    
    public function rankings()
    {
        $rankings = $this->getHealthyRankings(50); // Top 50
        
        return view('league.rankings', compact('rankings'));
    }
    
    private function getHealthyRankings($limit = 10)
    {
        return User::select('users.*')
            ->selectRaw('
                COUNT(posts.id) as total_posts,
                AVG(COALESCE(posts.health_score, 0)) as avg_health_score,
                SUM(CASE WHEN posts.health_score >= 80 THEN 10 
                         WHEN posts.health_score >= 60 THEN 5 
                         WHEN posts.health_score >= 40 THEN 2 
                         ELSE 0 END) as health_points,
                MAX(posts.created_at) as last_post_date
            ')
            ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at', 'users.updated_at')
            ->orderByDesc('health_points')
            ->orderByDesc('avg_health_score')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                $user->rank = $index + 1;
                $user->badge = $this->getBadgeForRank($index + 1);
                $user->level = $this->getLevelForPoints($user->health_points);
                return $user;
            });
    }
    
    private function getUserRank($userId)
    {
        $rankings = $this->getHealthyRankings(1000); // Large limit to find user
        
        foreach ($rankings as $index => $user) {
            if ($user->id == $userId) {
                return [
                    'position' => $index + 1,
                    'health_points' => $user->health_points,
                    'avg_health_score' => $user->avg_health_score,
                    'total_posts' => $user->total_posts,
                    'badge' => $user->badge,
                    'level' => $user->level
                ];
            }
        }
        
        return [
            'position' => null,
            'health_points' => 0,
            'avg_health_score' => 0,
            'total_posts' => 0,
            'badge' => '🥉',
            'level' => 'Débutant'
        ];
    }
    
    private function getTopScorerThisWeek()
    {
        return User::select('users.name')
            ->selectRaw('AVG(posts.health_score) as avg_score')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->where('posts.created_at', '>=', now()->subWeek())
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('avg_score')
            ->first();
    }
    
    private function getBadgeForRank($rank)
    {
        if ($rank == 1) return '👑';
        if ($rank <= 3) return '🏆';
        if ($rank <= 10) return '🥇';
        if ($rank <= 25) return '🥈';
        return '🥉';
    }
    
    private function getLevelForPoints($points)
    {
        if ($points >= 500) return 'Maître Healthy';
        if ($points >= 200) return 'Expert Nutrition';
        if ($points >= 100) return 'Pro Wellness';
        if ($points >= 50) return 'Amateur Sain';
        if ($points >= 20) return 'Apprenti Healthy';
        return 'Débutant';
    }
}