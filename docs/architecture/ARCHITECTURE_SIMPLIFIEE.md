# Architecture Simplifiée - DispoDialyse
## Pour un Développeur Débutant/Intermédiaire

**Version:** 2.0 - Architecture Simplifiée  
**Date:** 2025-12-10  
**Philosophie:** Simplicité maximale, maintenabilité par un seul développeur

---

## 🎯 Principe Directeur

**Cette architecture est conçue pour qu'UN SEUL développeur junior/intermédiaire puisse:**
- Comprendre tout le code en quelques jours
- Ajouter des fonctionnalités sans aide externe
- Déboguer rapidement les problèmes
- Maintenir le projet sur le long terme

**Règle d'or:** Si c'est compliqué, on ne le fait pas.

---

## 📚 Table des Matières

1. [Stack Technologique Ultra-Simplifiée](#1-stack-technologique-ultra-simplifiée)
2. [Architecture Générale](#2-architecture-générale)
3. [Structure du Projet](#3-structure-du-projet)
4. [Base de Données Simplifiée](#4-base-de-données-simplifiée)
5. [Modules Fonctionnels](#5-modules-fonctionnels)
6. [Guide de Développement](#6-guide-de-développement)
7. [Plan d'Implémentation Progressif](#7-plan-dimplémentation-progressif)

---

## 1. Stack Technologique Ultra-Simplifiée

### 1.1 Frontend: **HTML + CSS + JavaScript Vanilla + Alpine.js**

**Pourquoi PAS React/Vue/Angular ?**
- Trop de concepts abstraits (virtual DOM, state management, build tools)
- Nécessite webpack/vite, npm, transpilation
- Dépendances lourdes et fragiles
- Difficile à déboguer pour un débutant

**Pourquoi Alpine.js ?**
```html
<!-- Exemple: Alpine.js rend le JavaScript intuitif -->
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Contenu visible</div>
</div>
```

✅ **Avantages:**
- Pas de build, pas de compilation
- JavaScript directement dans le HTML (comme jQuery mais moderne)
- Courbe d'apprentissage de 1 heure
- Débogage dans le navigateur directement
- Taille: 15kb vs 150kb pour React

**Technologies Complémentaires:**
- **Tailwind CSS via CDN**: Styling simple sans écrire de CSS
- **HTMX**: Requêtes AJAX sans JavaScript complexe
- **Chart.js**: Graphiques simples
- **FullCalendar**: Planning (bibliothèque standalone)

### 1.2 Backend: **PHP 8.3 avec Laravel**

**Pourquoi PAS Node.js/NestJS ?**
- Async/await et callbacks complexes pour débutants
- TypeScript ajoute une couche d'abstraction
- Package.json avec 500 dépendances
- Problèmes de versions npm récurrents

**Pourquoi Laravel ?**

✅ **Simplicité extrême:**
```php
// Route en 1 ligne
Route::get('/rooms', [RoomController::class, 'index']);

// Contrôleur simple
class RoomController extends Controller {
    public function index() {
        $rooms = Room::all(); // Eloquent ORM ultra-simple
        return view('rooms.index', ['rooms' => $rooms]);
    }
}
```

✅ **Tout intégré:**
- ORM (Eloquent) sans configuration
- Authentification prête à l'emploi
- Migration base de données intégrée
- Cache, queue, email tout inclus
- Documentation la meilleure du marché

✅ **Débogage facile:**
- Messages d'erreur clairs
- Laravel Debugbar (barre debug visuelle)
- `dd()` pour déboguer en 1 fonction

**Alternative considérée:** Symfony (trop complexe), CodeIgniter (trop vieux)

### 1.3 Base de Données: **MySQL 8.0**

**Pourquoi PAS PostgreSQL ?**
- PostgreSQL est excellent MAIS MySQL est plus simple pour débuter
- Plus de tutoriels, plus d'hébergeurs
- phpMyAdmin (interface visuelle) est parfait pour débutants

**Pourquoi PAS de Redis/Elasticsearch ?**
- **Pas de Redis**: Le cache Laravel en fichiers suffit pour 100 utilisateurs
- **Pas d'Elasticsearch**: MySQL Full-Text Search suffit largement

### 1.4 Authentification: **Laravel Breeze**

**Solution clé en main:**
```bash
# Installation en 1 commande
php artisan breeze:install blade
```

Donne immédiatement:
- Login/Register/Forgot Password
- 2FA intégré (via Laravel Fortify)
- Protection CSRF automatique
- Sessions sécurisées

**SSO Active Directory:** Package Laravel `adldap2/adldap2-laravel` (configuration simple)

### 1.5 Temps Réel: **Laravel Echo + Pusher (ou Laravel Websockets)**

**Solution Laravel native:**
```php
// Backend: Diffuser un événement
broadcast(new SessionCreated($session));

// Frontend: Écouter l'événement
Echo.channel('planning')
    .listen('SessionCreated', (e) => {
        // Recharger le planning
        location.reload();
    });
```

**Options:**
- **Pusher**: Service hébergé (1000 connexions gratuites)
- **Laravel Websockets**: Auto-hébergé gratuit

### 1.6 Infrastructure: **Hébergement Mutualisé ou VPS Simple**

**Pas de Docker/Kubernetes:**
- Docker ajoute complexité (Dockerfile, docker-compose, volumes)
- VPS avec cPanel/Plesk suffit largement

**Hébergement recommandé:**
- **OVHcloud Hosting Performance** (certifié HDS possible)
- **Serveur dédié** si besoin on-premise
- Déploiement: FTP ou Git simple

**Sauvegarde:**
- Cron job mysqldump quotidien
- Copie fichiers sur serveur distant (rsync)

### 1.7 Récapitulatif Stack Simplifiée

```
┌─────────────────────────────────────────┐
│         FRONTEND                        │
│  - HTML5 + Tailwind CSS                 │
│  - Alpine.js (réactivité)               │
│  - HTMX (AJAX simplifié)                │
│  - FullCalendar (planning)              │
│  - Chart.js (graphiques)                │
└─────────────────────────────────────────┘
                  │
            HTTPS (Laravel)
                  │
┌─────────────────▼─────────────────────────┐
│         BACKEND                           │
│  - PHP 8.3                                │
│  - Laravel 11 (framework tout-en-un)     │
│  - Eloquent ORM                           │
│  - Blade (templates)                      │
│  - Laravel Echo (WebSocket)               │
└───────────────────────────────────────────┘
                  │
┌─────────────────▼─────────────────────────┐
│         BASE DE DONNÉES                   │
│  - MySQL 8.0                              │
│  - Cache fichiers Laravel                 │
└───────────────────────────────────────────┘
```

**Nombre total de technologies à apprendre:** 5
- PHP (langage simple)
- Laravel (framework bien documenté)
- MySQL (SQL basique)
- Alpine.js (JavaScript simple)
- Tailwind CSS (CSS utilitaire)

---

## 2. Architecture Générale

### 2.1 Pattern: **MVC Classique (pas de Clean Architecture)**

**Pourquoi SIMPLE MVC ?**
- 3 concepts seulement: Model, View, Controller
- Pas de Domain, Application, Infrastructure layers
- Pas d'inversion de dépendances
- Pas de Design Patterns complexes

```
Requête HTTP
    ↓
ROUTE (routes/web.php)
    ↓
CONTROLLER (app/Http/Controllers/)
    ↓
MODEL (app/Models/) ← → BASE DE DONNÉES
    ↓
VIEW (resources/views/)
    ↓
Réponse HTML
```

### 2.2 Organisation des Fichiers Laravel

```
dispodialyse/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/          # TOUS les contrôleurs ici
│   │   │   ├── PlanningController.php
│   │   │   ├── RoomController.php
│   │   │   ├── StaffController.php
│   │   │   ├── TransmissionController.php
│   │   │   ├── OnCallController.php
│   │   │   ├── DocumentController.php
│   │   │   └── MessageController.php
│   │   │
│   │   ├── Middleware/           # Authentification, permissions
│   │   └── Requests/             # Validation formulaires
│   │
│   ├── Models/                   # TOUS les modèles ici
│   │   ├── User.php
│   │   ├── Room.php
│   │   ├── Session.php
│   │   ├── Staff.php
│   │   ├── Transmission.php
│   │   ├── OnCall.php
│   │   ├── Document.php
│   │   └── Message.php
│   │
│   └── Services/                 # Logique métier (optionnel)
│       └── NotificationService.php
│
├── database/
│   ├── migrations/               # Structure base de données
│   └── seeders/                  # Données de test
│
├── resources/
│   └── views/                    # TOUS les templates HTML
│       ├── layouts/
│       │   └── app.blade.php    # Template principal
│       ├── planning/
│       │   ├── index.blade.php  # Liste planning
│       │   └── show.blade.php   # Détail session
│       ├── staff/
│       ├── transmissions/
│       ├── oncall/
│       ├── documents/
│       └── messages/
│
├── routes/
│   ├── web.php                  # TOUTES les routes ici
│   └── api.php                  # API si nécessaire
│
├── public/
│   ├── css/                     # CSS compilé
│   ├── js/                      # JavaScript
│   └── images/
│
├── config/                      # Configuration Laravel
├── storage/                     # Fichiers uploadés, logs
└── tests/                       # Tests (optionnel phase 1)
```

**Principe:** Tout est à sa place logique, pas de sous-dossiers complexes

### 2.3 Flux de Données Typique

**Exemple: Créer une session de dialyse**

```
1. User clique "Nouvelle session"
   ↓
2. Route: POST /sessions
   routes/web.php: Route::post('/sessions', [SessionController::class, 'store']);
   ↓
3. Controller valide et sauvegarde
   SessionController.php:
   public function store(Request $request) {
       $validated = $request->validate([
           'room_id' => 'required|exists:rooms,id',
           'start_time' => 'required|date',
           'staff_ids' => 'required|array'
       ]);
       
       $session = Session::create($validated);
       
       // Envoyer notifications
       NotificationService::notifyStaff($session);
       
       return redirect()->route('planning.index')
           ->with('success', 'Session créée');
   }
   ↓
4. Model enregistre en base
   Session.php utilise Eloquent ORM automatiquement
   ↓
5. Redirection vers planning avec message succès
```

**Total de fichiers touchés:** 3 maximum (Route, Controller, Model)

---

## 3. Structure du Projet Détaillée

### 3.1 Contrôleurs (app/Http/Controllers/)

**Un contrôleur par module fonctionnel:**

```php
// PlanningController.php - Gestion planning salles
class PlanningController extends Controller
{
    public function index()        // Liste planning
    public function show($id)      // Détail session
    public function create()       // Formulaire création
    public function store()        // Sauvegarde
    public function edit($id)      // Formulaire édition
    public function update($id)    // Mise à jour
    public function destroy($id)   // Suppression
}

// RoomController.php - Gestion salles
class RoomController extends Controller
{
    public function index()        // Liste salles
    public function create()       // Formulaire nouvelle salle
    public function store()        // Sauvegarde salle
    // etc.
}
```

**Règle:** Maximum 7 méthodes par contrôleur (CRUD standard Laravel)

### 3.2 Modèles (app/Models/)

**Un modèle = une table base de données:**

```php
// Session.php
class Session extends Model
{
    protected $fillable = [
        'room_id',
        'start_time',
        'end_time',
        'patient_reference',
        'type',
        'notes'
    ];
    
    // Relations simples
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    
    public function staff()
    {
        return $this->belongsToMany(Staff::class);
    }
}
```

**Eloquent fait TOUT le SQL automatiquement:**
```php
// Requêtes ultra-simples
$sessions = Session::with('room', 'staff')->get();
$todaySessions = Session::whereDate('start_time', today())->get();
$session = Session::find($id);
```

### 3.3 Vues (resources/views/)

**Blade templates - comme du HTML avec variables:**

```blade
{{-- resources/views/planning/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Planning Salles de Dialyse</h1>
    
    {{-- Calendrier FullCalendar --}}
    <div id="calendar"></div>
    
    @foreach($sessions as $session)
        <div class="bg-white p-4 rounded shadow mb-2">
            <h3>Salle {{ $session->room->name }}</h3>
            <p>{{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}</p>
            <p>Patient: {{ $session->patient_reference }}</p>
        </div>
    @endforeach
</div>
@endsection
```

**Alpine.js pour interactivité:**
```html
<div x-data="{ showModal: false }">
    <button @click="showModal = true" class="btn btn-primary">
        Nouvelle Session
    </button>
    
    <div x-show="showModal" class="modal">
        <!-- Formulaire création -->
    </div>
</div>
```

### 3.4 Routes (routes/web.php)

**TOUTES les routes dans UN seul fichier:**

```php
// routes/web.php

// Authentification (Breeze)
require __DIR__.'/auth.php';

// Planning
Route::middleware(['auth'])->group(function () {
    
    // Planning salles (module principal)
    Route::resource('planning', PlanningController::class);
    Route::get('/planning/calendar/data', [PlanningController::class, 'calendarData']);
    
    // Salles
    Route::resource('rooms', RoomController::class);
    
    // Annuaire personnel
    Route::resource('staff', StaffController::class);
    Route::get('/staff/search', [StaffController::class, 'search']);
    
    // Transmissions patients
    Route::resource('transmissions', TransmissionController::class);
    Route::post('/transmissions/{id}/acknowledge', [TransmissionController::class, 'acknowledge']);
    
    // Planning de garde
    Route::resource('oncall', OnCallController::class);
    Route::get('/oncall/current', [OnCallController::class, 'current']);
    
    // Documents
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/search', [DocumentController::class, 'search']);
    
    // Messagerie
    Route::resource('messages', MessageController::class);
    Route::post('/messages/{id}/read', [MessageController::class, 'markAsRead']);
});
```

**Principe:** Si c'est une page web accessible, c'est dans `routes/web.php`

---

## 4. Base de Données Simplifiée

### 4.1 Schéma Relationnel Minimal

**10 tables principales (pas 50):**

```sql
1. users               -- Utilisateurs (authentification)
2. rooms               -- Salles de dialyse
3. sessions            -- Séances de dialyse
4. session_staff       -- Table pivot sessions ↔ staff
5. staff               -- Personnel (profil détaillé)
6. transmissions       -- Transmissions patients
7. oncalls             -- Planning de garde
8. documents           -- Documents/protocoles
9. messages            -- Messagerie interne
10. audit_logs         -- Logs d'audit
```

### 4.2 Migrations Laravel (définition tables)

**Exemple: Table sessions**

```php
// database/migrations/2024_xx_xx_create_sessions_table.php
public function up()
{
    Schema::create('sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('room_id')->constrained()->onDelete('cascade');
        $table->dateTime('start_time');
        $table->dateTime('end_time');
        $table->string('patient_reference')->nullable(); // Anonymisé
        $table->enum('type', ['hemodialysis', 'hemodiafiltration', 'peritoneal']);
        $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled']);
        $table->text('notes')->nullable();
        $table->timestamps(); // created_at, updated_at automatiques
    });
}
```

**Créer la table:**
```bash
php artisan migrate
```

**Modifier la table:**
```bash
php artisan make:migration add_isolation_to_sessions
```

### 4.3 Relations Entre Tables

**Eloquent gère les relations automatiquement:**

```php
// Session appartient à une Room
class Session extends Model {
    public function room() {
        return $this->belongsTo(Room::class);
    }
}

// Room a plusieurs Sessions
class Room extends Model {
    public function sessions() {
        return $this->hasMany(Session::class);
    }
}

// Session a plusieurs Staff (many-to-many)
class Session extends Model {
    public function staff() {
        return $this->belongsToMany(Staff::class, 'session_staff');
    }
}
```

**Utilisation:**
```php
$session = Session::find(1);
echo $session->room->name;           // Accès relation
$staffMembers = $session->staff;     // Collection staff

// Requête avec relations (N+1 optimisé automatiquement)
$sessions = Session::with('room', 'staff')->get();
```

### 4.4 Exemple Complet: Table Users

```php
// Migration
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', [
        'super_admin',
        'admin_fonctionnel',
        'cadre_sante',
        'medecin',
        'infirmier',
        'aide_soignant',
        'secretariat',
        'technicien'
    ]);
    $table->string('first_name');
    $table->string('last_name');
    $table->string('phone')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('mfa_enabled')->default(false);
    $table->string('mfa_secret')->nullable();
    $table->timestamp('last_login_at')->nullable();
    $table->timestamps();
});

// Model
class User extends Authenticatable
{
    protected $fillable = [
        'username', 'email', 'password', 'role',
        'first_name', 'last_name', 'phone'
    ];
    
    protected $hidden = ['password', 'mfa_secret'];
    
    // Helper méthodes
    public function isAdmin() {
        return in_array($this->role, ['super_admin', 'admin_fonctionnel']);
    }
    
    public function canManagePlanning() {
        return in_array($this->role, ['cadre_sante', 'medecin', 'infirmier']);
    }
}
```

---

## 5. Modules Fonctionnels

### 5.1 Module Planning (Cœur du Système)

**Fichiers impliqués:**

```
app/Http/Controllers/PlanningController.php
app/Models/Session.php
app/Models/Room.php
resources/views/planning/index.blade.php
resources/views/planning/create.blade.php
public/js/planning-calendar.js
```

**Fonctionnalités:**

1. **Vue Calendrier (FullCalendar)**
```javascript
// public/js/planning-calendar.js
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'fr',
        events: '/planning/calendar/data', // API Laravel
        editable: true,
        droppable: true,
        eventDrop: function(info) {
            // Mise à jour via AJAX
            fetch(`/planning/${info.event.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    start_time: info.event.start,
                    end_time: info.event.end
                })
            });
        }
    });
    calendar.render();
});
```

2. **Contrôleur Simple**
```php
class PlanningController extends Controller
{
    public function index()
    {
        $sessions = Session::with('room', 'staff')
            ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();
        
        return view('planning.index', compact('sessions'));
    }
    
    public function calendarData(Request $request)
    {
        $sessions = Session::with('room')
            ->whereBetween('start_time', [$request->start, $request->end])
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'title' => "Salle {$session->room->name} - {$session->patient_reference}",
                    'start' => $session->start_time,
                    'end' => $session->end_time,
                    'backgroundColor' => $this->getColorByType($session->type)
                ];
            });
        
        return response()->json($sessions);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'patient_reference' => 'nullable|string',
            'type' => 'required|in:hemodialysis,hemodiafiltration,peritoneal',
            'staff_ids' => 'required|array|min:1',
            'notes' => 'nullable|string'
        ]);
        
        // Vérifier conflits
        $conflict = Session::where('room_id', $validated['room_id'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->exists();
        
        if ($conflict) {
            return back()->withErrors(['error' => 'Conflit de réservation détecté']);
        }
        
        $session = Session::create($validated);
        $session->staff()->attach($validated['staff_ids']);
        
        // Notification temps réel
        broadcast(new SessionCreated($session));
        
        return redirect()->route('planning.index')
            ->with('success', 'Session créée avec succès');
    }
}
```

### 5.2 Module Annuaire Personnel

**Super simple avec recherche:**

```php
class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->orderBy('last_name')
            ->paginate(20);
        
        return view('staff.index', compact('staff'));
    }
    
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $staff = Staff::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('role', 'like', "%{$query}%")
            ->get();
        
        return response()->json($staff);
    }
}
```

**Vue avec Alpine.js pour recherche instantanée:**

```blade
<div x-data="{
    query: '',
    results: [],
    async search() {
        if (this.query.length < 2) return;
        const response = await fetch(`/staff/search?q=${this.query}`);
        this.results = await response.json();
    }
}">
    <input 
        x-model="query" 
        @input.debounce.300ms="search()"
        type="text" 
        placeholder="Rechercher personnel..."
        class="w-full p-3 border rounded"
    >
    
    <div class="mt-4 grid grid-cols-3 gap-4">
        <template x-for="person in results" :key="person.id">
            <div class="bg-white p-4 rounded shadow">
                <h3 x-text="`${person.first_name} ${person.last_name}`"></h3>
                <p x-text="person.role" class="text-gray-600"></p>
                <p x-text="person.phone" class="text-blue-600"></p>
            </div>
        </template>
    </div>
</div>
```

### 5.3 Module Transmissions Patients

**Formulaire structuré avec alertes:**

```php
class TransmissionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_reference' => 'required|string',
            'category' => 'required|in:logistique,comportement,clinique',
            'priority' => 'required|in:normale,importante,urgente',
            'content' => 'required|string',
            'vital_signs' => 'nullable|array',
            'vital_signs.blood_pressure_systolic' => 'nullable|numeric',
            'vital_signs.blood_pressure_diastolic' => 'nullable|numeric',
            'vital_signs.heart_rate' => 'nullable|numeric',
            'vital_signs.temperature' => 'nullable|numeric'
        ]);
        
        $transmission = Transmission::create($validated);
        
        // Vérifier seuils d'alerte
        if ($this->checkAlertThresholds($transmission)) {
            // Notifier équipe
            NotificationService::sendAlert($transmission);
        }
        
        return redirect()->route('transmissions.index')
            ->with('success', 'Transmission enregistrée');
    }
    
    private function checkAlertThresholds($transmission)
    {
        $vitals = $transmission->vital_signs;
        
        if (!$vitals) return false;
        
        // Seuils configurables
        if (isset($vitals['blood_pressure_systolic']) && $vitals['blood_pressure_systolic'] < 90) {
            return true;
        }
        
        if (isset($vitals['heart_rate']) && ($vitals['heart_rate'] < 50 || $vitals['heart_rate'] > 120)) {
            return true;
        }
        
        return false;
    }
}
```

### 5.4 Module Messagerie Simple

**Conversations basiques (pas Slack/Teams):**

```php
class MessageController extends Controller
{
    public function index()
    {
        $conversations = Message::where('recipient_id', auth()->id())
            ->orWhere('sender_id', auth()->id())
            ->with('sender', 'recipient')
            ->latest()
            ->paginate(20);
        
        return view('messages.index', compact('conversations'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string'
        ]);
        
        $message = Message::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body']
        ]);
        
        // Notification temps réel si destinataire connecté
        broadcast(new NewMessage($message));
        
        return redirect()->route('messages.index')
            ->with('success', 'Message envoyé');
    }
}
```

---

## 6. Guide de Développement

### 6.1 Ajouter une Nouvelle Fonctionnalité (Exemple Complet)

**Scénario:** Ajouter la gestion des absences du personnel

**Étapes:**

1. **Créer la migration (structure table)**
```bash
php artisan make:migration create_absences_table
```

```php
// database/migrations/xxxx_create_absences_table.php
public function up()
{
    Schema::create('absences', function (Blueprint $table) {
        $table->id();
        $table->foreignId('staff_id')->constrained()->onDelete('cascade');
        $table->date('start_date');
        $table->date('end_date');
        $table->enum('type', ['conge', 'maladie', 'formation']);
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->text('reason')->nullable();
        $table->timestamps();
    });
}
```

```bash
php artisan migrate
```

2. **Créer le modèle**
```bash
php artisan make:model Absence
```

```php
// app/Models/Absence.php
class Absence extends Model
{
    protected $fillable = ['staff_id', 'start_date', 'end_date', 'type', 'status', 'reason'];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
```

3. **Créer le contrôleur**
```bash
php artisan make:controller AbsenceController --resource
```

```php
// app/Http/Controllers/AbsenceController.php
class AbsenceController extends Controller
{
    public function index()
    {
        $absences = Absence::with('staff')
            ->orderBy('start_date', 'desc')
            ->paginate(15);
        
        return view('absences.index', compact('absences'));
    }
    
    public function create()
    {
        $staff = Staff::where('is_active', true)->orderBy('last_name')->get();
        return view('absences.create', compact('staff'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:conge,maladie,formation',
            'reason' => 'nullable|string'
        ]);
        
        Absence::create($validated);
        
        return redirect()->route('absences.index')
            ->with('success', 'Absence enregistrée');
    }
    
    public function approve($id)
    {
        $absence = Absence::findOrFail($id);
        $absence->update(['status' => 'approved']);
        
        return back()->with('success', 'Absence approuvée');
    }
}
```

4. **Ajouter les routes**
```php
// routes/web.php
Route::resource('absences', AbsenceController::class);
Route::post('absences/{id}/approve', [AbsenceController::class, 'approve'])->name('absences.approve');
```

5. **Créer les vues**
```blade
{{-- resources/views/absences/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Gestion des Absences</h1>
        <a href="{{ route('absences.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">
            Nouvelle Absence
        </a>
    </div>
    
    <div class="bg-white rounded shadow">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Personnel</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Du</th>
                    <th class="p-3 text-left">Au</th>
                    <th class="p-3 text-left">Statut</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absences as $absence)
                <tr class="border-b">
                    <td class="p-3">{{ $absence->staff->full_name }}</td>
                    <td class="p-3">{{ $absence->type }}</td>
                    <td class="p-3">{{ $absence->start_date->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $absence->end_date->format('d/m/Y') }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-sm
                            @if($absence->status == 'approved') bg-green-100 text-green-800
                            @elseif($absence->status == 'rejected') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $absence->status }}
                        </span>
                    </td>
                    <td class="p-3">
                        @if($absence->status == 'pending' && auth()->user()->isAdmin())
                        <form action="{{ route('absences.approve', $absence) }}" method="POST" class="inline">
                            @csrf
                            <button class="text-green-600 hover:underline">Approuver</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $absences->links() }}
    </div>
</div>
@endsection
```

**Total temps:** 1-2 heures pour un développeur débutant

### 6.2 Déboguer un Problème

**Laravel Debugbar - Outil visuel:**

```bash
composer require barryvdh/laravel-debugbar --dev
```

Affiche automatiquement en bas de page:
- Requêtes SQL exécutées avec temps
- Variables disponibles dans la vue
- Routes disponibles
- Logs
- Temps d'exécution

**dd() - Debug and Die:**
```php
// Arrête l'exécution et affiche la variable
$sessions = Session::all();
dd($sessions); // Affiche structure complète, puis stop

// Continue après affichage
dump($sessions);
```

**Logs Laravel:**
```php
// Écrire dans storage/logs/laravel.log
Log::info('Session créée', ['session_id' => $session->id]);
Log::error('Erreur lors de la création', ['error' => $e->getMessage()]);
```

### 6.3 Tests Simples (Optionnel Phase 1)

**Test unitaire simple:**

```php
// tests/Feature/PlanningTest.php
class PlanningTest extends TestCase
{
    public function test_user_can_create_session()
    {
        $user = User::factory()->create(['role' => 'infirmier']);
        $room = Room::factory()->create();
        
        $response = $this->actingAs($user)->post('/planning', [
            'room_id' => $room->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(4),
            'type' => 'hemodialysis',
            'staff_ids' => [$user->id]
        ]);
        
        $response->assertRedirect('/planning');
        $this->assertDatabaseHas('sessions', [
            'room_id' => $room->id
        ]);
    }
}
```

```bash
php artisan test
```

---

## 7. Plan d'Implémentation Progressif

### Phase 1: MVP Core (Mois 1-3)

**Objectif:** Planning fonctionnel + Auth

**Étapes:**

1. **Semaine 1-2: Setup & Authentification**
   - Installer Laravel
   - Configurer base de données MySQL
   - Installer Laravel Breeze (auth complète)
   - Créer migrations users, roles
   - Tester login/register/logout

2. **Semaine 3-4: Module Planning Base**
   - Migration tables rooms, sessions
   - CRUD salles (create, read, update, delete)
   - CRUD sessions
   - Vue liste simple

3. **Semaine 5-6: Planning Avancé**
   - Intégrer FullCalendar
   - Drag & drop sessions
   - Détection conflits basique
   - Vue hebdomadaire/journalière

4. **Semaine 7-8: Staff Assignment**
   - Migration table staff
   - Assigner staff aux sessions (many-to-many)
   - Afficher équipe dans calendrier

5. **Semaine 9-10: Permissions & Rôles**
   - Middleware permissions
   - Protéger routes selon rôles
   - Afficher/masquer éléments UI selon rôle

6. **Semaine 11-12: Tests & Debug**
   - Tester chaque fonctionnalité
   - Corriger bugs
   - Optimiser performances
   - Documentation code

**Livrable:** Planning fonctionnel utilisable en production

### Phase 2: Modules Complémentaires (Mois 4-6)

1. **Annuaire Personnel (2 semaines)**
   - CRUD staff détaillé
   - Recherche multi-critères
   - Profils avec photo

2. **Transmissions Patients (3 semaines)**
   - Formulaires transmission
   - Historique patient
   - Alertes sur seuils

3. **Planning de Garde (2 semaines)**
   - Planning de garde simple
   - Vue "Qui est de garde ?"

4. **Messagerie Interne (3 semaines)**
   - Messages privés
   - Notifications temps réel (Laravel Echo)

**Livrable:** Plateforme complète modules essentiels

### Phase 3: Fonctionnalités Avancées (Mois 7-9)

1. **Documents & Protocoles (2 semaines)**
   - Upload documents
   - Catégorisation
   - Recherche full-text

2. **Notifications Push (2 semaines)**
   - Notifications navigateur
   - Email notifications
   - Configuration préférences

3. **Statistiques & Reporting (2 semaines)**
   - Dashboards avec Chart.js
   - Taux occupation salles
   - Export Excel

4. **PWA & Mobile (3 semaines)**
   - Service Worker
   - Installation app
   - Mode offline basique

**Livrable:** Plateforme feature-complete

### Phase 4: Conformité & Sécurité (Mois 10-12)

1. **Sécurité Avancée (4 semaines)**
   - Audit trail complet
   - 2FA avec Laravel Fortify
   - SSO Active Directory
   - Chiffrement champs sensibles

2. **Conformité RGPD (3 semaines)**
   - Export données personnelles
   - Droit à l'oubli
   - Consentements
   - Documentation

3. **Accessibilité WCAG (2 semaines)**
   - Audit accessibilité
   - Corrections (contraste, navigation clavier)
   - Tests avec lecteurs écran

4. **Tests & Documentation (3 semaines)**
   - Tests automatisés (coverage > 80%)
   - Documentation utilisateur
   - Documentation technique
   - Formation équipe

**Livrable:** Plateforme production-ready certifiée

---

## 8. Ressources d'Apprentissage

### 8.1 Apprendre Laravel (Débutant → Intermédiaire)

**Gratuit:**
1. **Laravel Bootcamp** (officiel): https://bootcamp.laravel.com/
   - Tutoriel complet en français
   - Construit une appli complète
   - Temps: 2-3 jours

2. **Laracasts** (freemium): https://laracasts.com/
   - Laravel from Scratch (série gratuite)
   - Vidéos courtes et claires
   - Temps: 1 semaine

3. **Documentation Laravel** (excellente): https://laravel.com/docs
   - En français: https://laravel.sillo.org/
   - Exemples pour chaque feature
   - Temps: référence continue

**Payant (recommandé):**
- **Laracasts** (15$/mois): Meilleur investissement pour Laravel
- **Udemy - Laravel courses**: Cours complets 20-30h

### 8.2 Apprendre Alpine.js

**Ultra-rapide:**
1. **Documentation officielle**: https://alpinejs.dev/
   - Se lit en 1 heure
   - 15 directives seulement

2. **Alpine.js Toolbox**: https://www.alpine-toolbox.com/
   - Exemples copy-paste

### 8.3 Communauté & Support

**Forums:**
- **Laravel.io**: Forum communauté Laravel
- **Laracasts Forum**: Très actif, réponses rapides
- **Stack Overflow**: Tag [laravel]

**Discord/Slack:**
- Laravel Discord (officiel)
- Laravel France (communauté FR)

---

## 9. Checklist Maintenabilité

### ✅ Code Propre

- [ ] Noms de variables explicites (`$session` pas `$s`)
- [ ] Fonctions courtes (max 20 lignes)
- [ ] Commentaires en français expliquant le "pourquoi"
- [ ] Pas de code dupliqué (DRY)
- [ ] Indentation cohérente (4 espaces)

### ✅ Structure

- [ ] Un contrôleur = une ressource
- [ ] Routes dans `web.php` seulement
- [ ] Pas de logique dans les vues (Blade)
- [ ] Modèles Eloquent simples (relations only)

### ✅ Sécurité

- [ ] Validation TOUS les inputs (`$request->validate()`)
- [ ] CSRF activé (automatique Laravel)
- [ ] Pas de SQL raw (utiliser Eloquent)
- [ ] Mots de passe hashés (automatique Laravel)
- [ ] Protection XSS (automatique Blade `{{ }}`)

### ✅ Performance

- [ ] Eager loading (`with()`) pour relations
- [ ] Pagination activée (`paginate()`)
- [ ] Cache vues Blade (automatique)
- [ ] Index sur colonnes searchées

### ✅ Documentation

- [ ] README.md avec instructions setup
- [ ] Commentaires au-dessus des fonctions complexes
- [ ] `.env.example` avec variables requises
- [ ] CHANGELOG.md pour modifications

---

## 10. Déploiement Simplifié

### 10.1 Hébergement Recommandé

**Option 1: Hébergement Mutualisé (Plus simple)**

**OVHcloud Hosting Performance:**
- PHP 8.3 ✅
- MySQL 8.0 ✅
- Certificat SSL gratuit ✅
- Accès SSH ✅
- Prix: ~10€/mois

**Déploiement:**
```bash
# Local: Build application
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Upload via FTP ou Git
git push ovhcloud main

# Sur serveur via SSH
cd /home/www
php artisan migrate --force
php artisan storage:link
```

**Option 2: VPS Simple (Plus de contrôle)**

**OVHcloud VPS Starter:**
- Ubuntu 22.04
- 2 vCores, 2GB RAM
- Prix: ~6€/mois

**Installation Laravel Forge (optionnel):**
- Service qui configure VPS automatiquement
- Déploiement Git en 1 clic
- Prix: 12$/mois

### 10.2 Sauvegarde Automatique

**Script cron mysqldump:**

```bash
# /home/backup/backup-db.sh
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -ppassword dispodialyse > /home/backup/db_$DATE.sql
gzip /home/backup/db_$DATE.sql

# Garder 30 jours
find /home/backup/ -name "db_*.sql.gz" -mtime +30 -delete

# Copier sur serveur distant
rsync -avz /home/backup/db_$DATE.sql.gz backup-server:/backups/
```

**Crontab:**
```bash
# Backup quotidien 2h du matin
0 2 * * * /home/backup/backup-db.sh
```

### 10.3 Monitoring Simple

**UptimeRobot (gratuit):**
- Ping toutes les 5 minutes
- Alertes email/SMS si down
- https://uptimerobot.com/

**Laravel Health Check:**
```php
// routes/web.php
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'ok', 'database' => 'connected']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'database' => 'disconnected'], 500);
    }
});
```

---

## 11. Évolutions Futures

### Quand Complexifier ?

**Ne PAS complexifier tant que:**
- < 500 utilisateurs actifs simultanés
- Temps réponse < 2 secondes
- Charge serveur < 70%
- Vous arrivez à ajouter features facilement

**Signes qu'il faut évoluer:**
- Ralentissements fréquents
- Impossible d'ajouter features sans casser le code
- Base de données > 100 Go
- Plus de 1000 utilisateurs simultanés

### Migration Progressive

**Si besoin de scaler:**

1. **Ajouter Redis (facile)**
```bash
composer require predis/predis
```
```php
// config/cache.php: changer driver
'default' => 'redis'
```

2. **Séparer base de données lecture/écriture**
```php
// config/database.php
'mysql' => [
    'write' => ['host' => 'master-db'],
    'read' => [
        ['host' => 'replica1-db'],
        ['host' => 'replica2-db']
    ]
]
```

3. **CDN pour assets statiques**
- Cloudflare (gratuit)
- Configure domaine, active CDN
- CSS/JS/images servis depuis CDN

4. **Microservices (dernier recours)**
- Extraire module notifications en API séparée
- Module messagerie en service séparé
- Garder planning dans monolithe

---

## Conclusion

### Résumé Architecture Simplifiée

**Stack Finale:**
```
Frontend: HTML + Tailwind CSS + Alpine.js + HTMX
Backend: PHP 8.3 + Laravel 11
Database: MySQL 8.0
Cache: Fichiers Laravel (Redis si nécessaire)
Auth: Laravel Breeze + Fortify (2FA)
Temps réel: Laravel Echo + Pusher
Hébergement: VPS OVHcloud ou hébergement mutualisé
```

**Nombre de technologies:** 5 essentielles (PHP, Laravel, MySQL, Alpine.js, Tailwind)

**Temps pour devenir autonome:** 2-3 semaines de formation Laravel

**Maintenabilité:** ✅ Un développeur junior peut maintenir seul

### Différences avec Architecture Complexe

| Aspect | Architecture Complexe | Architecture Simple |
|--------|----------------------|---------------------|
| Frontend | React + TypeScript + Vite + 20 libs | Alpine.js + Tailwind |
| Backend | NestJS + TypeScript + DDD | Laravel PHP |
| Base de données | PostgreSQL + Redis + Elasticsearch | MySQL only |
| Conteneurs | Docker + Kubernetes | VPS simple ou mutualisé |
| Build | Webpack/Vite, transpilation | Aucun build frontend |
| Courbe apprentissage | 3-6 mois | 2-3 semaines |
| Nombre de fichiers config | 15-20 | 5-7 |
| Dépendances | 500+ npm packages | 20 Composer packages |

### Points Non-Négociables (Restent)

✅ **Sécurité:**
- HTTPS/TLS 1.3
- 2FA obligatoire
- RBAC avec 8 rôles
- Audit trail complet
- Conformité RGPD

✅ **Performance:**
- < 2s chargement
- Temps réel (WebSocket)
- Responsive mobile

✅ **Accessibilité:**
- WCAG 2.1 AA
- Navigation clavier
- Lecteur écran

### Prochaines Étapes

1. **Validation Architecture** avec équipe
2. **Setup Environnement Développement**
   ```bash
   composer create-project laravel/laravel dispodialyse
   cd dispodialyse
   php artisan serve
   ```
3. **Formation Laravel** (2-3 semaines)
4. **Début Phase 1**: Module Planning

---

**Cette architecture est conçue pour DURER et être MAINTENABLE sur 5-10 ans par une petite équipe ou un développeur seul.**

**Questions? Besoin de clarifications? Tout est documenté et explicable simplement.**