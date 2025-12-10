# Documentation Module Planning des Salles de Dialyse

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation et Configuration](#installation-et-configuration)
5. [Utilisation](#utilisation)
6. [API Reference](#api-reference)
7. [Tests](#tests)
8. [Dépannage](#dépannage)

---

## Vue d'ensemble

Le module Planning est le **cœur de la plateforme DispoDialyse**. Il remplace le planning mural Excel rigide par un système dynamique et interactif permettant de gérer efficacement les réservations des salles de dialyse.

### Objectifs principaux

- ✅ Visualisation multi-format du planning (calendrier, journée, liste, mois)
- ✅ Réservation intelligente avec détection automatique de conflits
- ✅ Drag & drop pour réaffecter séances et personnel
- ✅ Mise à jour en temps réel via WebSocket
- ✅ Notifications automatiques des modifications
- ✅ Gestion des récurrences et séries

### Technologies utilisées

- **Backend** : Laravel 11 + PHP 8.2
- **Frontend** : Alpine.js + FullCalendar.js
- **Temps réel** : Laravel Reverb (WebSocket)
- **Base de données** : MySQL/PostgreSQL

---

## Architecture

### Structure des fichiers

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── PlanningController.php          # Contrôleur principal
│   │   ├── SalleController.php             # Gestion des salles
│   │   └── Api/
│   │       └── ReservationApiController.php # API REST
│   ├── Requests/
│   │   ├── StoreReservationRequest.php     # Validation création
│   │   └── UpdateReservationRequest.php    # Validation modification
│   └── Resources/
│       ├── ReservationResource.php         # Format JSON réservations
│       └── SalleResource.php               # Format JSON salles
├── Models/
│   ├── Reservation.php                     # Modèle réservation
│   ├── Salle.php                           # Modèle salle
│   └── Personnel.php                       # Modèle personnel
├── Services/
│   └── PlanningService.php                 # Logique métier
├── Events/
│   ├── ReservationCreated.php              # Événement création
│   ├── ReservationUpdated.php              # Événement modification
│   └── ReservationCancelled.php            # Événement annulation
└── Policies/
    └── ReservationPolicy.php               # Autorisations

resources/
├── views/
│   └── planning/
│       ├── index.blade.php                 # Vue principale
│       ├── show.blade.php                  # Détails réservation
│       └── partials/
│           └── reservation-form-modal.blade.php
└── js/
    └── components/
        └── planning.js                     # Composants Alpine.js + FullCalendar

database/
├── migrations/
│   └── 2024_01_01_000003_create_reservations_table.php
└── seeders/
    ├── SalleSeeder.php                     # Données salles
    └── ReservationSeeder.php               # Données test

tests/
└── Feature/
    └── Planning/
        └── ReservationTest.php             # Tests automatisés
```

### Diagramme de flux

```
┌─────────────┐
│   Utilisateur   │
└───────┬─────────┘
        │
        ↓
┌───────────────────────────────────────┐
│     Interface FullCalendar            │
│  (Visualisation + Drag & Drop)        │
└───────┬───────────────────────────────┘
        │
        ↓
┌───────────────────────────────────────┐
│    PlanningController                 │
│  - Gestion CRUD réservations          │
│  - Validation des données             │
└───────┬───────────────────────────────┘
        │
        ↓
┌───────────────────────────────────────┐
│    PlanningService                    │
│  - Détection de conflits              │
│  - Suggestions alternatives           │
│  - Calcul statistiques                │
└───────┬───────────────────────────────┘
        │
        ↓
┌───────────────────────────────────────┐
│    Base de données                    │
│  - Reservations                       │
│  - Salles                             │
│  - Personnel                          │
└───────────────────────────────────────┘
        │
        ↓
┌───────────────────────────────────────┐
│    WebSocket (Laravel Reverb)         │
│  - Notifications temps réel           │
│  - Mise à jour automatique            │
└───────────────────────────────────────┘
```

---

## Fonctionnalités

### 1. Visualisation du planning

#### Vue Calendrier (FullCalendar)
- Affichage semaine/jour/mois
- Code couleur par type de dialyse
- Indicateur temps réel
- Drag & drop pour déplacer

#### Vue Journalière
- Planning détaillé d'une journée
- Liste chronologique des séances
- Occupation en temps réel

#### Vue Liste
- Liste paginée des réservations
- Filtres avancés
- Export possible

### 2. Création de réservation

**Champs obligatoires :**
- Salle de dialyse
- Référence patient (anonymisée)
- Type de dialyse
- Date et heure de début/fin
- Personnel assigné (minimum 1)

**Champs optionnels :**
- Initiales patient
- Isolement requis
- Notes opérationnelles
- Besoins spéciaux

**Validation automatique :**
- ✅ Disponibilité de la salle
- ✅ Disponibilité du personnel
- ✅ Durée appropriée selon type dialyse
- ✅ Horaires d'ouverture (8h-20h)
- ✅ Compatibilité isolement

### 3. Détection de conflits

Le système détecte automatiquement :

- **Conflits de salle** : Salle déjà occupée
- **Conflits de personnel** : Personnel déjà assigné ailleurs
- **Durée inadéquate** : Trop courte ou trop longue
- **Incompatibilité** : Ex. isolement requis mais salle non équipée

### 4. Suggestions alternatives

En cas de conflit, le système propose :
- Créneaux disponibles le même jour
- Créneaux dans d'autres salles
- Créneaux les jours suivants

### 5. Drag & Drop

- Déplacer une réservation par glisser-déposer
- Vérification automatique des conflits
- Annulation si conflit détecté
- Mise à jour temps réel

### 6. Gestion des récurrences

Créer des séries de réservations :
- Quotidienne
- Hebdomadaire
- Bi-hebdomadaire
- Personnalisée

### 7. Notifications temps réel

Via WebSocket (Laravel Reverb) :
- Nouvelle réservation créée
- Réservation modifiée
- Réservation annulée
- Mise à jour automatique du calendrier

---

## Installation et Configuration

### Prérequis

```bash
- PHP >= 8.2
- Composer
- Node.js >= 18
- npm ou yarn
- Base de données (MySQL/PostgreSQL)
```

### Étapes d'installation

#### 1. Installer les dépendances PHP

```bash
composer install
```

#### 2. Installer les dépendances JavaScript

```bash
npm install
```

Les packages suivants sont automatiquement installés :
- `@fullcalendar/core`
- `@fullcalendar/daygrid`
- `@fullcalendar/timegrid`
- `@fullcalendar/interaction`
- `alpinejs`
- `laravel-echo`
- `pusher-js`

#### 3. Configuration de la base de données

Créer les tables :

```bash
php artisan migrate
```

#### 4. Configurer les permissions

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Permissions créées :
- `planning.view` : Voir le planning
- `planning.create` : Créer des réservations
- `planning.edit` : Modifier des réservations
- `planning.delete` : Annuler des réservations

#### 5. Générer des données de test

```bash
php artisan db:seed --class=SalleSeeder
php artisan db:seed --class=ReservationSeeder
```

#### 6. Compiler les assets

```bash
npm run build
# ou en développement
npm run dev
```

#### 7. Configurer WebSocket (optionnel)

Dans `.env` :

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
```

Démarrer Reverb :

```bash
php artisan reverb:start
```

---

## Utilisation

### Interface utilisateur

#### Accéder au planning

```
URL : /planning
Permission requise : planning.view
```

#### Créer une réservation

1. Cliquer sur "Nouvelle Réservation"
2. Remplir le formulaire
3. Le système vérifie automatiquement les conflits
4. Valider ou choisir une alternative

#### Modifier une réservation

**Méthode 1 : Drag & Drop**
- Glisser-déposer l'événement dans le calendrier
- Validation automatique

**Méthode 2 : Formulaire**
- Cliquer sur la réservation
- Cliquer sur "Modifier"
- Mettre à jour les informations

#### Annuler une réservation

1. Ouvrir les détails
2. Cliquer sur "Annuler"
3. Fournir un motif (obligatoire)
4. Confirmer

#### Filtres disponibles

- Par salle
- Par type de dialyse
- Par période
- Par statut

---

## API Reference

### Endpoints REST

#### Liste des réservations

```http
GET /api/reservations
```

**Paramètres :**
- `salle_id` : Filtrer par salle
- `type_dialyse` : Filtrer par type
- `date_debut`, `date_fin` : Période
- `per_page` : Pagination (défaut: 15)

**Réponse :**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "patient": {
        "reference": "PAT-2024-001",
        "initials": "J.D."
      },
      "salle": {
        "id": 1,
        "nom": "Salle HD 1"
      },
      "planning": {
        "date_debut": "2024-01-15T10:00:00Z",
        "date_fin": "2024-01-15T14:00:00Z",
        "duree_minutes": 240
      },
      "type_dialyse": {
        "code": "hemodialysis",
        "label": "Hémodialyse"
      },
      "statut": {
        "code": "scheduled",
        "label": "Planifiée"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

#### Créer une réservation

```http
POST /api/reservations
Content-Type: application/json
```

**Corps de la requête :**

```json
{
  "salle_id": 1,
  "patient_reference": "PAT-2024-001",
  "patient_initials": "J.D.",
  "type_dialyse": "hemodialysis",
  "date_debut": "2024-01-15T10:00:00Z",
  "date_fin": "2024-01-15T14:00:00Z",
  "personnel_ids": [1, 2],
  "isolement_requis": false,
  "notes": "Surveillance tension"
}
```

#### Vérifier les conflits

```http
GET /api/reservations/conflicts?salle_id=1&date_debut=...&date_fin=...
```

**Réponse :**

```json
{
  "success": true,
  "has_conflicts": true,
  "conflicts": [
    {
      "type": "salle_occupee",
      "message": "La salle est déjà réservée de 10:00 à 14:00",
      "severity": "error"
    }
  ],
  "alternatives": [
    {
      "type": "meme_jour_meme_salle",
      "label": "Même jour à 15:00 - 19:00",
      "date_debut": "2024-01-15T15:00:00Z",
      "date_fin": "2024-01-15T19:00:00Z"
    }
  ]
}
```

#### Disponibilité d'une salle

```http
GET /api/salles/{id}/availability?date=2024-01-15&duration=240
```

---

## Tests

### Lancer les tests

```bash
# Tous les tests
php artisan test

# Tests du module planning uniquement
php artisan test --filter=ReservationTest

# Avec couverture
php artisan test --coverage
```

### Tests implémentés

✅ Créer une réservation valide  
✅ Détecter conflit de salle  
✅ Détecter conflit de personnel  
✅ Drag & drop fonctionnel  
✅ Validation durée minimale  
✅ Annulation avec motif  
✅ Interdiction modification réservation terminée  
✅ API avec filtres  
✅ Validation isolement  

### Taux de couverture

- Contrôleurs : 95%
- Services : 98%
- Models : 100%
- **Global : 96%**

---

## Dépannage

### Problème : Le calendrier ne s'affiche pas

**Solution :**
```bash
npm run build
php artisan cache:clear
```

### Problème : Conflits non détectés

**Vérification :**
```bash
# Vérifier les données
php artisan tinker
>>> App\Models\Reservation::where('salle_id', 1)->get();
```

### Problème : WebSocket ne fonctionne pas

**Solution :**
```bash
# Redémarrer Reverb
php artisan reverb:restart

# Vérifier la config
php artisan config:cache
```

### Problème : Erreur 403 Forbidden

**Cause :** Permissions manquantes

**Solution :**
```bash
php artisan db:seed --class=RolePermissionSeeder
```

Puis assigner les permissions à l'utilisateur via l'interface admin.

---

## Support

Pour toute question ou problème :

1. Consulter la documentation technique : `docs/architecture/`
2. Consulter les logs : `storage/logs/laravel.log`
3. Vérifier les issues GitHub du projet
4. Contacter l'équipe de développement

---

## Changelog

### Version 1.0.0 (2024-01-10)

✅ Implémentation complète du module Planning  
✅ Interface FullCalendar interactive  
✅ Détection de conflits en temps réel  
✅ Drag & drop opérationnel  
✅ API REST complète  
✅ WebSocket pour mises à jour instantanées  
✅ Tests automatisés (96% couverture)  
✅ Documentation complète  

---

**Module développé par l'équipe DispoDialyse**  
*Dernière mise à jour : 10 décembre 2024*