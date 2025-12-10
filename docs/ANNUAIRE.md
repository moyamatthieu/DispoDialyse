# 📖 Module Annuaire du Personnel - Documentation

## Vue d'ensemble

Le module **Annuaire du Personnel** est un système centralisé de gestion des profils du personnel médical et administratif. Il remplace les multiples annuaires papier obsolètes par une solution numérique dynamique et toujours à jour.

## Fonctionnalités principales

### 🔍 Recherche multi-critères
- Recherche textuelle avec fuzzy matching
- Filtres par fonction, service, disponibilité
- Autocomplete intelligent
- Recherche par compétence
- Recherche par numéro de téléphone

### 👥 Vues multiples
- **Liste** : Tableau détaillé avec pagination
- **Trombinoscope** : Galerie de photos avec contact rapide
- **Organigramme** : Vue hiérarchique par service et fonction

### 📋 Gestion des fiches
- Création et modification de fiches personnel
- Upload et gestion de photos de profil
- Gestion des qualifications, certifications et langues
- Historique des modifications (via audit logs)
- Soft delete (archivage)

### 📊 Fonctionnalités avancées
- Export CSV de l'annuaire
- API REST complète
- Indicateurs de disponibilité en temps réel
- Personnel de garde
- Statistiques par service

## Architecture

### Modèle de données

Le modèle [`Personnel`](../app/Models/Personnel.php) contient :

```php
// Identité
first_name, last_name, photo_url

// Contact
email_pro, phone_office, phone_mobile, phone_pager, extension

// Professionnel
job_title, specialty, department, employment_type
qualifications (JSON), certifications (JSON), languages (JSON)

// Statut
is_active, hire_date, leave_date

// Relations
user_id (lien avec compte utilisateur optionnel)
```

### Contrôleurs

#### 1. [`PersonnelController`](../app/Http/Controllers/PersonnelController.php)
Contrôleur principal pour les vues web.

**Méthodes :**
- `index()` : Liste avec filtres
- `show()` : Fiche détaillée
- `create()` / `store()` : Création
- `edit()` / `update()` : Modification
- `destroy()` : Archivage
- `export()` : Export CSV
- `organigramme()` : Vue organigramme
- `trombinoscope()` : Vue galerie
- `disponibilite()` : Statut en temps réel

#### 2. [`PersonnelApiController`](../app/Http/Controllers/Api/PersonnelApiController.php)
API REST pour intégrations et appels AJAX.

**Endpoints :**
```
GET /api/personnel - Liste
GET /api/personnel/{id} - Détails
GET /api/personnel/search - Recherche
GET /api/personnel/autocomplete - Suggestions
GET /api/personnel/disponibles - Personnel disponible
GET /api/personnel/de-garde - Personnel de garde
GET /api/personnel/par-competence/{competence} - Par compétence
GET /api/personnel/organigramme - Données organigramme
GET /api/personnel/statistiques/{service} - Stats service
```

#### 3. [`RecherchePersonnelController`](../app/Http/Controllers/RecherchePersonnelController.php)
Recherche avancée et fonctionnalités spécialisées.

### Services

#### [`PersonnelService`](../app/Services/PersonnelService.php)
Logique métier centralisée.

**Méthodes principales :**
```php
search(string $query, array $filters): Collection
findByCompetence(string $competence): Collection
findDisponibles(?Carbon $date): Collection
findDeGarde(?Carbon $date): Collection
getStatistiquesService(string $service): array
getTauxPresence(Personnel $personnel, Carbon $debut, Carbon $fin): float
buildOrganigramme(): array
uploadPhoto(Personnel $personnel, UploadedFile $file): string
deletePhoto(Personnel $personnel): bool
exportToCsv(Collection $personnel): string
```

### Validation

#### [`StorePersonnelRequest`](../app/Http/Requests/StorePersonnelRequest.php)
Validation pour la création.

**Règles principales :**
- Prénom, nom, fonction, service : obligatoires
- Email professionnel : unique, format email
- Téléphone fixe : format français (10 chiffres)
- Téléphone mobile : format 06/07
- Photo : image max 2 Mo
- Date d'embauche : date passée obligatoire

#### [`UpdatePersonnelRequest`](../app/Http/Requests/UpdatePersonnelRequest.php)
Validation pour la modification (email unique ignorant l'ID actuel).

### Resources API

#### [`PersonnelResource`](../app/Http/Resources/PersonnelResource.php)
Format JSON pour liste (informations essentielles).

#### [`PersonnelDetailResource`](../app/Http/Resources/PersonnelDetailResource.php)
Format JSON pour fiche complète (toutes informations).

## Utilisation

### Routes Web

```php
// Annuaire
Route::get('/annuaire', [PersonnelController::class, 'index'])
    ->name('annuaire.index');

Route::get('/annuaire/{personnel}', [PersonnelController::class, 'show'])
    ->name('annuaire.show');

Route::get('/annuaire/export', [PersonnelController::class, 'export'])
    ->name('annuaire.export');

// CRUD (admin uniquement)
Route::post('/annuaire', [PersonnelController::class, 'store'])
    ->name('annuaire.store')
    ->middleware('can:personnel.create');
```

### Utilisation de l'API

#### Recherche de personnel
```javascript
// Recherche simple
const response = await fetch('/api/personnel/search?q=Martin');
const data = await response.json();

// Avec filtres
const response = await fetch('/api/personnel/search?q=Martin&service=Dialyse&disponibilite=disponible');
```

#### Autocomplete
```javascript
const response = await fetch('/api/recherche/personnel/autocomplete?q=Mar');
const suggestions = await response.json();
// Retourne: [{ id, label, sublabel, photo, value }, ...]
```

#### Personnel disponible
```javascript
const response = await fetch('/api/recherche/personnel/disponibles');
const data = await response.json();
// Retourne: { success, data: [...], date, count }
```

#### Personnel de garde
```javascript
const response = await fetch('/api/recherche/personnel/de-garde');
const data = await response.json();
```

### Composants Alpine.js

#### Recherche avec filtres
```html
<div x-data="personnelSearch()">
    <input x-model="searchQuery" @input.debounce.500ms="search()">
    <select x-model="filters.service" @change="search()">
        <!-- Options -->
    </select>
</div>
```

#### Organigramme
```html
<div x-data="organigrammeData()" x-init="init()">
    <template x-for="service in organigramme">
        <!-- Affichage -->
    </template>
</div>
```

## Permissions

Le module utilise le système de permissions Laravel Gate :

- `personnel.view` : Consulter l'annuaire
- `personnel.create` : Créer des fiches
- `personnel.edit` : Modifier des fiches
- `personnel.delete` : Archiver des fiches (admin uniquement)
- `personnel.export` : Exporter en CSV

## Vues Blade

### Structure des vues

```
resources/views/annuaire/
├── index.blade.php          # Liste principale avec onglets
├── show.blade.php           # Fiche détaillée
├── create.blade.php         # Formulaire création
├── edit.blade.php           # Formulaire modification
└── partials/
    ├── form.blade.php       # Formulaire partagé
    ├── liste.blade.php      # Vue tableau
    ├── trombinoscope.blade.php  # Vue galerie
    └── organigramme.blade.php   # Vue hiérarchique
```

### Personnalisation des vues

Les vues utilisent Tailwind CSS et Alpine.js. Pour personnaliser :

1. **Couleurs** : Modifier les classes Tailwind
2. **Icônes** : Remplacer les emojis par Font Awesome ou autre
3. **Layout** : Adapter la grille responsive

## Seeding

Pour peupler l'annuaire avec des données de test :

```bash
php artisan db:seed --class=PersonnelSeeder
```

Crée 20 fiches personnel réalistes avec :
- Variété de fonctions et services
- Qualifications et certifications
- Langues parlées
- Hiérarchie organisationnelle

## Tests

Exécuter les tests :

```bash
# Tous les tests du module
php artisan test tests/Feature/Annuaire/PersonnelTest.php

# Test spécifique
php artisan test --filter test_peut_afficher_liste_personnel
```

**Tests couverts :**
- ✅ Affichage liste et fiche
- ✅ Recherche et filtres
- ✅ Création avec validation
- ✅ Modification et archivage
- ✅ Upload de photo
- ✅ Export CSV
- ✅ API REST
- ✅ Permissions

## Performance

### Optimisations implémentées

1. **Index de base de données**
   - Index sur `last_name`, `first_name`
   - Index sur `is_active`
   - Index sur `department`
   - Index full-text pour recherche

2. **Eager Loading**
   ```php
   Personnel::with('user', 'gardes')->get();
   ```

3. **Pagination**
   - 20 résultats par page par défaut
   - Configurable via query param `per_page`

4. **Cache des photos**
   - Redimensionnement automatique (400x400)
   - Compression JPEG qualité 85%

### Recommandations

- Utiliser Redis pour le cache des recherches fréquentes
- Implémenter un CDN pour les photos
- Ajouter un système de mise en cache des organigrammes

## Sécurité

### Mesures implémentées

1. **Validation stricte** : Tous les inputs sont validés
2. **CSRF Protection** : Tokens sur tous les formulaires
3. **Authorization** : Policy Laravel pour chaque action
4. **SQL Injection** : Utilisation d'Eloquent ORM
5. **XSS Protection** : Échappement automatique Blade
6. **Upload sécurisé** : Validation type MIME et taille

### Audit

Toutes les modifications sont tracées via [`AuditLog`](../app/Models/AuditLog.php) :
- Qui a modifié
- Quand
- Quelles données
- Depuis quelle IP

## Maintenance

### Tâches régulières

1. **Nettoyage des photos orphelines**
   ```bash
   php artisan storage:link
   php artisan annuaire:clean-photos
   ```

2. **Vérification données obsolètes**
   ```bash
   php artisan annuaire:check-inactive
   ```

3. **Export backup**
   ```bash
   php artisan annuaire:backup-export
   ```

## Évolutions futures

### Fonctionnalités envisagées

- [ ] Import CSV en masse
- [ ] Synchronisation avec Active Directory
- [ ] QR Code pour contact rapide
- [ ] Carte interactive du service
- [ ] Messagerie interne intégrée
- [ ] Gestion des compétences rares
- [ ] Alertes de garde automatiques
- [ ] Application mobile dédiée

## Support

Pour toute question ou problème :

1. Consulter les logs : `storage/logs/laravel.log`
2. Vérifier les permissions utilisateur
3. Consulter la documentation des dépendances
4. Contacter l'équipe de développement

## Changelog

### Version 1.0.0 (2024-01-01)
- ✨ Version initiale complète
- 📖 Annuaire centralisé
- 🔍 Recherche multi-critères
- 👥 Trois vues (liste, trombinoscope, organigramme)
- 📊 Export CSV
- 🔒 Gestion des permissions
- 🧪 Tests automatisés

---

**Dernière mise à jour** : 10 décembre 2024  
**Auteur** : Équipe DispoDialyse  
**Version** : 1.0.0