<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Définition des channels de broadcasting pour les notifications temps réel.
| Utilisé avec Laravel Reverb ou Pusher pour WebSocket.
|
*/

// Channel privé pour les notifications utilisateur
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel pour le planning global (tous les utilisateurs authentifiés)
Broadcast::channel('planning', function ($user) {
    return $user !== null;
});

// Channel pour une salle spécifique
Broadcast::channel('salle.{salleId}', function ($user, $salleId) {
    // Tous les utilisateurs authentifiés peuvent écouter
    return $user !== null;
});

// Channel pour les transmissions d'un patient spécifique
Broadcast::channel('transmissions.patient.{reference}', function ($user, $reference) {
    // Vérifier que l'utilisateur a le droit d'accéder aux transmissions
    return in_array($user->role->value, [
        'super_admin',
        'admin_fonctionnel',
        'cadre_sante',
        'medecin',
        'infirmier'
    ]);
});

// Channel pour les alertes urgentes (médical uniquement)
Broadcast::channel('alertes', function ($user) {
    return in_array($user->role->value, [
        'medecin',
        'infirmier',
        'cadre_sante'
    ]);
});