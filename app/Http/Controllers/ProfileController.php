<?php

namespace App\Http\Controllers;

use App\Services\CloudflareR2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'tag' => 'nullable|string|max:50|unique:users,tag,' . $user->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        // Mettre à jour les informations de base
        $user->name = $request->name;
        $user->tag = $request->tag;

        // Gestion de la photo de profil
        if ($request->hasFile('profile_picture')) {
            $r2Service = app(CloudflareR2Service::class);
            
            // Supprimer l'ancienne photo s'il existe
            if ($user->avatar) {
                try {
                    $urlParts = parse_url($user->avatar);
                    $avatarPath = ltrim($urlParts['path'] ?? '', '/');
                    if ($avatarPath) {
                        $r2Service->delete($avatarPath);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erreur suppression ancienne photo profil: ' . $e->getMessage());
                }
            }
            
            // Upload de la nouvelle photo
            $avatarUrl = $r2Service->uploadUserAvatar($request->file('profile_picture'), $user->id);
            $user->avatar = $avatarUrl;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil mis à jour avec succès !');
    }
} 