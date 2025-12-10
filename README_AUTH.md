# 🔐 Système d'Authentification DispoDialyse

## Installation Rapide

### 1. Installation des dépendances

```bash
composer install
npm install
```

### 2. Configuration

Copiez `.env.example` vers `.env` et configurez :

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Base de données

```bash
# Créer les tables
php artisan migrate

# Créer les rôles, permissions et utilisateurs de test
php artisan db:seed
```

### 4. Enregistrer les providers

Ajoutez dans `config/app.php` :

```php
'providers' => [
    // ...
    App\Providers\BladeServiceProvider::class,
],
```

### 5. Middleware dans `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'audit' => \App\Http\Middleware\AuditLog::class,
    ]);
})
```

### 6. Compiler les assets

```bash
npm run build
```

## 🎯 Comptes de test

Tous les comptes utilisent le mot de passe : `Password123!`

| Rôle | Username | Email |
|------|----------|-------|
| Super Admin | admin | admin@dispodialyse.fr |
| Admin Fonctionnel | admin.fonctionnel | admin.fonctionnel@dispodialyse.fr |
| Cadre de Santé | cadre.sante | cadre@dispodialyse.fr |
| Médecin | dr.bernard | medecin@dispodialyse.fr |
| Infirmier | infirmier.claire | infirmier@dispodialyse.fr |
| Aide-Soignant | as.thomas | aidesoignant@dispodialyse.fr |
| Secrétariat | secretariat.julie | secretariat@dispodialyse.fr |
| Technicien | tech.pierre | technicien@dispodialyse.fr |

## 📂 Structure des fichiers créés

### Contrôleurs d'authentification
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `app/Http/Controllers/Auth/PasswordResetLinkController.php`
- `app/Http/Controllers/Auth/NewPasswordController.php`
- `app/Http/Controllers/Auth/EmailVerificationPromptController.php`
- `app/Http/Controllers/Auth/VerifyEmailController.php`
- `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`
- `app/Http/Controllers/Auth/TwoFactorAuthenticationController.php`
- `app/Http/Controllers/DashboardController.php`

### Middleware
- `app/Http/Middleware/CheckRole.php` - Vérification des rôles
- `app/Http/Middleware/CheckPermission.php` - Vérification des permissions
- `app/Http/Middleware/AuditLog.php` - Logs d'audit automatiques

### Policies
- `app/Policies/ReservationPolicy.php`
- `app/Policies/PersonnelPolicy.php`
- `app/Policies/TransmissionPolicy.php`
- `app/Policies/GardePolicy.php`
- `app/Policies/DocumentPolicy.php`
- `app/Policies/MessagePolicy.php`

### Services et Traits
- `app/Services/AuditService.php` - Service d'audit RGPD
- `app/Traits/HasAuditLog.php` - Trait pour logs automatiques
- `app/Helpers/AuthHelper.php` - Fonctions helper

### Seeders
- `database/seeders/RolePermissionSeeder.php` - Rôles et permissions
- `database/seeders/UserSeeder.php` - Utilisateurs de test
- `database/seeders/DatabaseSeeder.php` - Seeder principal

### Vues
- `resources/views/layouts/app.blade.php` - Layout principal
- `resources/views/layouts/guest.blade.php` - Layout authentification
- `resources/views/layouts/components/navigation.blade.php`
- `resources/views/layouts/components/sidebar.blade.php`
- `resources/views/layouts/components/user-dropdown.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/forgot-password.blade.php`
- `resources/views/auth/reset-password.blade.php`
- `resources/views/auth/verify-email.blade.php`
- `resources/views/auth/two-factor-challenge.blade.php`
- `resources/views/dashboard.blade.php`

### Routes
- `routes/auth.php` - Routes d'authentification
- `routes/web.php` - Routes protégées par permissions

### Providers
- `app/Providers/BladeServiceProvider.php` - Directives Blade personnalisées

## 🔑 Matrice des permissions

| Module | Super Admin | Admin Fonct. | Cadre Santé | Médecin | Infirmier | Aide-Soign. | Secrétariat | Technicien |
|--------|-------------|--------------|-------------|---------|-----------|-------------|-------------|------------|
| Planning | ✅ CRUD | ✅ CRUD | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ Lecture | ✅ CRUD | 👁️ Lecture |
| Personnel | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ Lecture | 👁️ Lecture | 👁️ Lecture | ✅ CRUD | 👁️ Lecture |
| Transmissions | ✅ CRUD | 👁️ Lecture | 👁️ Lecture | ✅ CRUD | ✅ CRUD | 👁️ Lecture | ❌ Aucun | ❌ Aucun |
| Gardes | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ Lecture | 👁️ Lecture | 👁️ Lecture | 👁️ Lecture | 👁️ Lecture |
| Documents | ✅ CRUD | ✅ CRUD | 👁️ Lecture | 👁️ Lecture | 👁️ + Upload | 👁️ Lecture | 👁️ + Upload | 👁️ Lecture |
| Messages | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Users | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Audit | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

## 🛠️ Utilisation

### Vérifier un rôle

```php
// Dans un contrôleur
if (auth()->user()->isAdmin()) {
    // Code pour admin
}

// Dans une route
Route::get('/admin', function () {
    // ...
})->middleware('role:super_admin,admin_fonctionnel');

// Dans une vue
@role('medecin')
    <p>Contenu réservé au médecin</p>
@endrole
```

### Vérifier une permission

```php
// Dans un contrôleur
if (auth()->user()->can('planning.create')) {
    // Peut créer
}

// Dans une route
Route::post('/planning', [PlanningController::class, 'store'])
    ->middleware('can:planning.create');

// Dans une vue
@can('planning.edit')
    <button>Modifier</button>
@endcan
```

### Utiliser les policies

```php
// Dans un contrôleur
$this->authorize('update', $reservation);

// Dans une vue
@can('update', $reservation)
    <a href="{{ route('planning.edit', $reservation) }}">Modifier</a>
@endcan
```

### Logs d'audit

```php
use App\Services\AuditService;

$auditService = app(AuditService::class);

// Log une action
$auditService->log(
    user: auth()->user(),
    action: 'consultation_dossier',
    description: 'Consultation du dossier patient'
);

// Recherche
$logs = $auditService->search([
    'user_id' => $userId,
    'date_from' => now()->subDays(7)
]);
```

## 📚 Documentation complète

Voir [`docs/AUTHENTIFICATION.md`](docs/AUTHENTIFICATION.md) pour la documentation complète incluant :
- Guide détaillé de chaque rôle
- Système d'authentification 2FA
- Conformité RGPD
- Bonnes pratiques de sécurité
- Configuration production

## ✅ Fonctionnalités implémentées

- [x] 8 rôles avec permissions granulaires
- [x] Authentification Laravel Breeze
- [x] Support 2FA (Google Authenticator)
- [x] Vérification d'email
- [x] Réinitialisation de mot de passe
- [x] Rate limiting (5 tentatives de connexion)
- [x] 6 Policies pour l'autorisation
- [x] Middleware de rôle et permission
- [x] Système d'audit complet (RGPD)
- [x] 10 utilisateurs de test
- [x] Layout responsive avec navigation par rôle
- [x] Dashboard adapté par rôle
- [x] Directives Blade personnalisées
- [x] Routes protégées par permissions
- [x] Logs de connexion/déconnexion
- [x] Sessions sécurisées
- [x] Protection CSRF

## 🚀 Prochaines étapes

1. **Tester l'authentification**
   ```bash
   php artisan serve
   # Accéder à http://localhost:8000/login
   # Tester avec admin@dispodialyse.fr / Password123!
   ```

2. **Personnaliser les vues**
   - Modifier les couleurs dans `tailwind.config.js`
   - Ajouter votre logo dans `public/images/`

3. **Implémenter les modules métier**
   - Planning des salles
   - Gestion du personnel
   - Transmissions patients
   - etc.

4. **Configuration production**
   - Changer tous les mots de passe
   - Activer HTTPS
   - Configurer la sauvegarde
   - Surveiller les logs

## ⚠️ Avertissements

- ❗ Les mots de passe par défaut sont **Password123!** - À CHANGER EN PRODUCTION
- ❗ Les emails de test utilisent des domaines @dispodialyse.fr - À adapter
- ❗ Le 2FA n'est pas activé par défaut - À activer pour les admins en production
- ❗ Les logs d'audit doivent être nettoyés régulièrement (RGPD)

## 📞 Support

Pour toute question ou problème, consultez :
- Documentation Laravel : https://laravel.com/docs
- Documentation Spatie Permission : https://spatie.be/docs/laravel-permission
- Documentation du projet : [`docs/AUTHENTIFICATION.md`](docs/AUTHENTIFICATION.md)

---

**Version** : 1.0.0  
**Date** : Décembre 2024  
**Licence** : Propriétaire