# Guide d'Authentification et RBAC - DispoDialyse

## Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Les 8 rôles du système](#les-8-rôles-du-système)
3. [Système d'authentification](#système-dauthentification)
4. [Permissions et autorisations](#permissions-et-autorisations)
5. [Utilisation des policies](#utilisation-des-policies)
6. [Directives Blade personnalisées](#directives-blade-personnalisées)
7. [Système d'audit](#système-daudit)
8. [Configuration et déploiement](#configuration-et-déploiement)

---

## Vue d'ensemble

DispoDialyse implémente un système complet de contrôle d'accès basé sur les rôles (RBAC) avec 8 rôles distincts, chacun ayant des permissions spécifiques adaptées à leurs responsabilités dans le service de dialyse.

### Technologies utilisées

- **Laravel 11** - Framework PHP
- **Spatie Laravel Permission** - Gestion des rôles et permissions
- **Spatie Laravel Activity Log** - Traçabilité et audit
- **Laravel Breeze** - Authentification de base
- **Alpine.js** - Interactivité frontend
- **Tailwind CSS** - Framework CSS

### Sécurité

- ✅ Authentification à deux facteurs (2FA) avec Google Authenticator
- ✅ Vérification d'email obligatoire
- ✅ Rate limiting sur les tentatives de connexion (5 max)
- ✅ Logs d'audit complets (conforme RGPD)
- ✅ Protection CSRF sur tous les formulaires
- ✅ Hashage sécurisé des mots de passe (bcrypt)
- ✅ Sessions sécurisées avec cookies HttpOnly

---

## Les 8 rôles du système

### 1. Super Administrateur (`super_admin`)

**Responsabilités :** Gestion complète du système

**Permissions :**
- ✅ Accès total à toutes les fonctionnalités
- ✅ Gestion des utilisateurs et rôles
- ✅ Configuration système
- ✅ Consultation des logs d'audit
- ✅ Toutes les permissions planning, personnel, transmissions, gardes, documents, messages

**Code couleur UI :** Rouge

### 2. Administrateur Fonctionnel (`admin_fonctionnel`)

**Responsabilités :** Gestion administrative et organisationnelle

**Permissions :**
- ✅ Planning complet (CRUD)
- ✅ Personnel complet (CRUD)
- ✅ Transmissions (lecture seule)
- ✅ Gardes complet
- ✅ Documents complet
- ✅ Messages
- ✅ Gestion utilisateurs
- ✅ Audit logs

**Code couleur UI :** Violet

### 3. Cadre de Santé (`cadre_sante`)

**Responsabilités :** Supervision du personnel et coordination des plannings

**Permissions :**
- ✅ Planning complet
- ✅ Personnel complet
- 👁️ Transmissions (lecture seule)
- ✅ Gardes complet
- 👁️ Documents (lecture seule)
- ✅ Messages
- ✅ Audit logs

**Code couleur UI :** Indigo

### 4. Médecin (`medecin`)

**Responsabilités :** Gestion médicale et validation des protocoles

**Permissions :**
- ✅ Planning complet
- 👁️ Personnel (lecture seule)
- ✅ Transmissions complet
- 👁️ Gardes (lecture seule)
- 👁️ Documents (lecture seule)
- ✅ Messages

**Code couleur UI :** Bleu

### 5. Infirmier (`infirmier`)

**Responsabilités :** Soins directs et gestion des séances

**Permissions :**
- ✅ Planning complet
- 👁️ Personnel (lecture seule)
- ✅ Transmissions complet
- 👁️ Gardes (lecture seule)
- 👁️ Documents (lecture + upload)
- ✅ Messages

**Code couleur UI :** Vert

### 6. Aide-Soignant (`aide_soignant`)

**Responsabilités :** Assistance aux soins

**Permissions :**
- 👁️ Planning (lecture seule)
- 👁️ Personnel (lecture seule)
- 👁️ Transmissions (lecture seule)
- 👁️ Gardes (lecture seule)
- 👁️ Documents (lecture seule)
- ✅ Messages

**Code couleur UI :** Cyan

### 7. Secrétariat (`secretariat`)

**Responsabilités :** Gestion administrative

**Permissions :**
- ✅ Planning complet
- ✅ Personnel complet
- ❌ Transmissions (aucun accès)
- 👁️ Gardes (lecture seule)
- 👁️ Documents (lecture + upload)
- ✅ Messages

**Code couleur UI :** Jaune

### 8. Technicien (`technicien`)

**Responsabilités :** Maintenance technique

**Permissions :**
- 👁️ Planning (lecture seule)
- 👁️ Personnel (lecture seule)
- ❌ Transmissions (aucun accès)
- 👁️ Gardes (lecture seule)
- 👁️ Documents (lecture seule)
- ✅ Messages

**Code couleur UI :** Gris

---

## Système d'authentification

### Connexion

**Route :** `GET /login`  
**Contrôleur :** `App\Http\Controllers\Auth\AuthenticatedSessionController@create`

```php
// Les utilisateurs se connectent avec leur username (pas email)
POST /login
{
    "username": "admin",
    "password": "Password123!",
    "remember": true
}
```

**Fonctionnalités :**
- Rate limiting (5 tentatives max)
- Enregistrement de la dernière connexion (IP + timestamp)
- Support 2FA optionnel
- Session "Se souvenir de moi"

### Déconnexion

```php
POST /logout
```

- Invalidation de la session
- Régénération du token CSRF
- Log d'audit de la déconnexion

### Réinitialisation de mot de passe

**Routes :**
```php
GET  /forgot-password  // Formulaire
POST /forgot-password  // Envoi du lien
GET  /reset-password/{token}  // Formulaire de réinitialisation
POST /reset-password  // Traitement
```

### Authentification à deux facteurs (2FA)

**Activation :**
```php
POST /two-factor/enable
```

**Challenge :**
```php
GET  /two-factor-challenge  // Affichage du formulaire
POST /two-factor-challenge  // Vérification du code
```

**Désactivation :**
```php
POST /two-factor/disable
```

---

## Permissions et autorisations

### Structure des permissions

Format : `{module}.{action}`

Exemples :
- `planning.view` - Voir le planning
- `planning.create` - Créer une réservation
- `planning.edit` - Modifier une réservation
- `planning.delete` - Supprimer une réservation

### Vérification des permissions dans le code

#### Dans les contrôleurs

```php
public function edit(Reservation $reservation)
{
    // Méthode 1 : Avec authorize()
    $this->authorize('update', $reservation);
    
    // Méthode 2 : Avec Gate
    if (Gate::denies('planning.edit')) {
        abort(403);
    }
    
    // Méthode 3 : Avec can()
    if (!auth()->user()->can('planning.edit')) {
        return redirect()->back()->with('error', 'Permission refusée');
    }
}
```

#### Dans les routes

```php
// Middleware de permission
Route::get('/planning', [PlanningController::class, 'index'])
    ->middleware('can:planning.view');

// Middleware de rôle
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:super_admin,admin_fonctionnel');
```

#### Dans les vues Blade

```blade
@can('planning.create')
    <a href="{{ route('planning.create') }}">Nouvelle réservation</a>
@endcan

@cannot('planning.edit')
    <p>Vous ne pouvez pas modifier</p>
@endcannot
```

---

## Utilisation des policies

Les policies définissent les autorisations au niveau modèle.

### Exemple : ReservationPolicy

```php
// app/Policies/ReservationPolicy.php

public function update(User $user, Reservation $reservation): bool
{
    // Peut modifier si :
    // - A la permission planning.edit
    // - ET (est admin OU a créé la réservation)
    return $user->can('planning.edit') && 
           ($user->isAdmin() || $reservation->created_by === $user->id);
}
```

### Utilisation dans un contrôleur

```php
public function update(Request $request, Reservation $reservation)
{
    $this->authorize('update', $reservation);
    
    // Code de mise à jour...
}
```

### Utilisation dans une vue

```blade
@can('update', $reservation)
    <a href="{{ route('planning.edit', $reservation) }}">Modifier</a>
@endcan
```

---

## Directives Blade personnalisées

### @role - Vérifier un rôle

```blade
@role('super_admin')
    <p>Contenu réservé au super admin</p>
@endrole

@role(['medecin', 'infirmier'])
    <p>Contenu pour le personnel médical</p>
@endrole
```

### @admin - Vérifier si administrateur

```blade
@admin
    <a href="{{ route('admin.settings') }}">Administration</a>
@endadmin
```

### @medical - Vérifier si personnel médical

```blade
@medical
    <p>Accès aux transmissions patients</p>
@endmedical
```

### @canmanageplanning - Gestion du planning

```blade
@canmanageplanning
    <button>Créer une réservation</button>
@endcanmanageplanning
```

---

## Système d'audit

### Logs automatiques

Toutes les actions sensibles sont automatiquement loggées :
- Connexion/déconnexion
- Création/modification/suppression d'entités
- Changements de permissions
- Accès aux données sensibles

### AuditService

```php
use App\Services\AuditService;

$auditService = app(AuditService::class);

// Log manuel
$auditService->log(
    user: $user,
    action: 'consultation_dossier_patient',
    auditableType: 'App\Models\Patient',
    auditableId: $patient->id,
    description: 'Consultation du dossier patient'
);

// Recherche dans les logs
$logs = $auditService->search([
    'user_id' => $userId,
    'action' => 'deleted',
    'date_from' => now()->subDays(7),
    'limit' => 100
]);

// Statistiques
$stats = $auditService->getStatistics();
```

### Conformité RGPD

```php
// Export des données utilisateur
$data = $auditService->exportUserLogs($user);

// Anonymisation (droit à l'oubli)
$auditService->anonymizeUserLogs($user);

// Nettoyage automatique (conservation 1 an)
$auditService->cleanOldLogs(daysToKeep: 365);
```

---

## Configuration et déploiement

### 1. Installation des dépendances

```bash
composer install
npm install
```

### 2. Configuration de l'environnement

```env
# .env
APP_NAME=DispoDialyse
APP_ENV=production
APP_DEBUG=false

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dispodialyse
DB_USERNAME=root
DB_PASSWORD=

# Mail pour réinitialisation mot de passe
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525

# Session sécurisée
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
```

### 3. Migrations et seeders

```bash
# Créer les tables
php artisan migrate

# Créer les rôles, permissions et utilisateurs de test
php artisan db:seed
```

### 4. Enregistrer le BladeServiceProvider

```php
// config/app.php
'providers' => [
    // ...
    App\Providers\BladeServiceProvider::class,
],
```

### 5. Configurer les middleware

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    'role' => \App\Http\Middleware\CheckRole::class,
    'permission' => \App\Http\Middleware\CheckPermission::class,
    'audit' => \App\Http\Middleware\AuditLog::class,
];
```

### 6. Compilation des assets

```bash
npm run build
```

---

## Comptes de test

Après avoir exécuté les seeders, les comptes suivants sont disponibles :

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Super Admin | admin@dispodialyse.fr | Password123! |
| Admin Fonctionnel | admin.fonctionnel@dispodialyse.fr | Password123! |
| Cadre de Santé | cadre@dispodialyse.fr | Password123! |
| Médecin | medecin@dispodialyse.fr | Password123! |
| Infirmier | infirmier@dispodialyse.fr | Password123! |
| Aide-Soignant | aidesoignant@dispodialyse.fr | Password123! |
| Secrétariat | secretariat@dispodialyse.fr | Password123! |
| Technicien | technicien@dispodialyse.fr | Password123! |

⚠️ **IMPORTANT** : Changez tous ces mots de passe en production !

---

## Bonnes pratiques de sécurité

### Production

1. **Mots de passe**
   - Changez tous les mots de passe par défaut
   - Imposez des mots de passe forts (8+ caractères, majuscules, chiffres, symboles)
   - Activez le 2FA pour les administrateurs

2. **Sessions**
   ```env
   SESSION_SECURE_COOKIE=true
   SESSION_LIFETIME=120  # 2 heures
   ```

3. **HTTPS**
   - Activez HTTPS obligatoire
   - Configurez HSTS

4. **Rate Limiting**
   - Laissez activé le rate limiting sur /login (5 tentatives)
   - Ajoutez des limites sur les API si nécessaire

5. **Logs**
   - Surveillez les logs d'audit régulièrement
   - Nettoyez les anciens logs (RGPD : 1 an max recommandé)

6. **Backup**
   - Sauvegardez régulièrement la base de données
   - Incluez les tables de permissions et d'audit

---

## Support et maintenance

Pour toute question ou problème :
- Consultez la documentation Laravel : https://laravel.com/docs
- Documentation Spatie Permission : https://spatie.be/docs/laravel-permission
- Contactez l'équipe de développement

---

**Version** : 1.0.0  
**Dernière mise à jour** : Décembre 2024  
**Auteur** : Équipe DispoDialyse