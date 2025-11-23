<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBadge;
use App\Models\Contribution;

class GamificationService
{
    /**
     * Configuration des badges avec des designs épiques
     */
    private static $badgeConfig = [
        'contribution_count' => [
            1 => [
                'threshold' => 10,
                'name' => 'Prix Découvreur',
                'emoji' => '🌟',
                'color' => 'from-yellow-400 to-orange-500',
                'description' => 'Premier pas dans la communauté ! 10 prix ajoutés'
            ],
            2 => [
                'threshold' => 50,
                'name' => 'Prix Explorateur',
                'emoji' => '🔍',
                'color' => 'from-blue-400 to-cyan-500',
                'description' => 'Tu explores bien ! 50 prix découverts'
            ],
            3 => [
                'threshold' => 100,
                'name' => 'Prix Chasseur',
                'emoji' => '🎯',
                'color' => 'from-green-400 to-emerald-500',
                'description' => 'Chasseur de bons plans ! 100 prix ajoutés'
            ],
            4 => [
                'threshold' => 250,
                'name' => 'Prix Expert',
                'emoji' => '💎',
                'color' => 'from-purple-400 to-pink-500',
                'description' => 'Expert des prix ! 250 contributions'
            ],
            5 => [
                'threshold' => 500,
                'name' => 'Prix Maître',
                'emoji' => '👑',
                'color' => 'from-yellow-500 to-orange-600',
                'description' => 'Maître des bonnes affaires ! 500 prix'
            ],
            6 => [
                'threshold' => 1000,
                'name' => 'Prix Légende',
                'emoji' => '🚀',
                'color' => 'from-indigo-500 to-purple-600',
                'description' => 'Légende vivante ! 1000 prix ajoutés'
            ],
            7 => [
                'threshold' => 2500,
                'name' => 'Prix Titan',
                'emoji' => '⚡',
                'color' => 'from-red-500 to-pink-600',
                'description' => 'Force de la nature ! 2500 contributions'
            ],
            8 => [
                'threshold' => 5000,
                'name' => 'Prix Divinité',
                'emoji' => '✨',
                'color' => 'from-yellow-400 via-pink-500 to-purple-600',
                'description' => 'Divinité des prix ! 5000 contributions épiques'
            ]
        ]
    ];

    /**
     * Vérifier et attribuer les badges après une contribution
     */
    public function checkAndAwardBadges(User $user): array
    {
        $newBadges = [];
        $contributionCount = $user->getTotalContributionsCount();

        foreach (self::$badgeConfig['contribution_count'] as $level => $config) {
            if ($contributionCount >= $config['threshold']) {
                // Vérifier si l'utilisateur a déjà ce badge
                $existingBadge = UserBadge::where('user_id', $user->id)
                    ->where('badge_type', 'contribution_count')
                    ->where('badge_level', $level)
                    ->first();

                if (!$existingBadge) {
                    $badge = UserBadge::create([
                        'user_id' => $user->id,
                        'badge_type' => 'contribution_count',
                        'badge_level' => $level,
                        'badge_name' => $config['name'],
                        'badge_emoji' => $config['emoji'],
                        'badge_color' => $config['color'],
                        'contributions_count' => $contributionCount
                    ]);

                    $newBadges[] = [
                        'badge' => $badge,
                        'config' => $config
                    ];
                }
            }
        }

        return $newBadges;
    }

    /**
     * Obtenir le prochain badge à débloquer
     */
    public function getNextBadge(User $user): ?array
    {
        $contributionCount = $user->getTotalContributionsCount();

        foreach (self::$badgeConfig['contribution_count'] as $level => $config) {
            if ($contributionCount < $config['threshold']) {
                return [
                    'level' => $level,
                    'name' => $config['name'],
                    'emoji' => $config['emoji'],
                    'threshold' => $config['threshold'],
                    'remaining' => $config['threshold'] - $contributionCount,
                    'progress' => round(($contributionCount / $config['threshold']) * 100, 1)
                ];
            }
        }

        return null; // Utilisateur a tous les badges
    }

    /**
     * Obtenir tous les badges de l'utilisateur
     */
    public function getUserBadges(User $user)
    {
        return $user->badges()->orderBy('badge_level', 'desc')->get();
    }

    /**
     * Obtenir les statistiques de gamification
     */
    public function getGamificationStats(User $user): array
    {
        $badges = $this->getUserBadges($user);
        $nextBadge = $this->getNextBadge($user);
        $totalContributions = $user->getTotalContributionsCount();

        return [
            'total_contributions' => $totalContributions,
            'badges_count' => $badges->count(),
            'latest_badge' => $badges->first(),
            'next_badge' => $nextBadge,
            'all_badges' => $badges,
            'completion_percentage' => $this->getCompletionPercentage($user)
        ];
    }

    /**
     * Calculer le pourcentage de complétion global
     */
    private function getCompletionPercentage(User $user): float
    {
        $maxLevel = max(array_keys(self::$badgeConfig['contribution_count']));
        $userMaxLevel = $user->badges()
            ->where('badge_type', 'contribution_count')
            ->max('badge_level') ?? 0;

        return round(($userMaxLevel / $maxLevel) * 100, 1);
    }

    /**
     * Générer une notification de badge stylée
     */
    public function generateBadgeNotification(array $badgeData): string
    {
        $badge = $badgeData['badge'];
        $config = $badgeData['config'];

        return "🎉 NOUVEAU BADGE DÉBLOQUÉ ! 🎉\n\n"
             . "{$config['emoji']} {$config['name']}\n"
             . "{$config['description']}\n\n"
             . "Tu deviens de plus en plus incroyable ! 🚀";
    }
} 