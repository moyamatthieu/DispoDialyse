# Guide de Démarrage Rapide - DispoDialyse

**Pour développeurs débutants/intermédiaires**  
**Temps estimé:** 1 journée pour setup complet

---

## 🎯 Objectif

Installer et lancer l'application en **local** pour commencer le développement.

---

## 📋 Prérequis

### Logiciels à Installer

1. **PHP 8.3+** (langage backend)
   - Windows: https://windows.php.net/download/
   - Mac: `brew install php`
   - Linux: `sudo apt install php8.3 php8.3-mysql php8.3-xml php8.3-mbstring`

2. **Composer** (gestionnaire de dépendances PHP)
   - https://getcomposer.org/download/
   - Vérifier: `composer --version`

3. **MySQL 8.0** (base de données)
   - Windows/Mac: https://dev.mysql.com/downloads/installer/
   - Linux: `sudo apt install mysql-server`
   - Ou **XAMPP** (inclut PHP + MySQL): https://www.apachefriends.org/

4. **Node.js 20+** (pour compilation assets frontend)
   - https://nodejs.org/
   - Vérifier: `node --version`

5. **Git** (contrôle de version)
   - https://git-scm.com/downloads

### Éditeur de Code Recommandé

**Visual Studio Code** (gratuit)
- https://code.visualstudio.com/
- Extensions utiles:
  - PHP Intelephense
  - Laravel Blade Snippets
  - Tailwind CSS IntelliSense
  - GitLens

---

## 🚀 Installation Étape par Étape

### Étape 1: Créer le Projet Laravel

```bash
# Ouvrir terminal/cmd dans dossier de travail
cd ~/projets

# Créer nouveau projet Laravel
composer create-project laravel/laravel dispodialyse

# Entrer dans le dossier
cd dispodialyse

# Ouvrir dans VSCode
code .
```

**Temps:** 2-3 minutes

### Étape 2: Configurer la Base de Données

#### 2.1 Créer la base MySQL

```sql
-- Ouvrir MySQL dans terminal ou phpMyAdmin
mysql -u root -p

-- Créer la base
CREATE DATABASE dispodialyse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Créer utilisateur
CREATE USER 'dispodialyse_app'@'localhost' IDENTIFIED BY 'VotreMotDePasse123!';
GRANT ALL PRIVILEGES ON dispodialyse.* TO 'dispodialyse_app'@'localhost';
FLUSH PRIVILEGES;

-- Quitter
EXIT;
```

#### 2.2 Configurer `.env`

```bash
# Copier le fichier exemple
cp .env.example .env

# Générer clé application
php artisan key:generate
```

**Éditer `.env`:**
```env
APP_NAME=DispoDialyse
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dispodialyse
DB_USERNAME=dispodialyse_app
DB_PASSWORD=VotreMotDePasse123!

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Étape 3: Installer Dépendances

```bash
# Dépendances PHP (backend)
composer install

# Dépendances JavaScript (frontend)
npm install
```

**Temps:** 5-10 minutes

### Étape 4: Installer Laravel Breeze (Authentification)

```bash
# Installer Breeze
composer require laravel/breeze --dev

# Installer stack Blade (HTML simple)
php artisan breeze:install blade

# Compiler assets
npm run dev
```

**Ce que ça fait:**
- Crée pages login/register/logout
- Ajoute système de session
- Configure routes d'authentification

### Étape 5: Créer les Tables (Migrations)

```bash
# Exécuter migrations (créer tables)
php artisan migrate

# Vous devriez voir:
# Migration table created successfully.
# Migrating: 2014_10_12_000000_create_users_table
# Migrated:  2014_10_12_000000_create_users_table
# ...
```

**Vérifier dans phpMyAdmin:**
- Ouvrir http://localhost/phpmyadmin
- Base `dispodialyse` doit avoir tables: users, password_resets, etc.

### Étape 6: Créer Utilisateur Test

```bash
php artisan tinker
```

```php
// Dans Tinker (console PHP interactive)
User::create([
    'name' => 'Admin Test',
    'email' => 'admin@test.fr',
    'password' => bcrypt('password123'),
    'role' => 'super_admin'
]);

// Appuyer Ctrl+C pour quitter
```

### Étape 7: Lancer le Serveur

```bash
# Terminal 1: Serveur PHP
php artisan serve

# Terminal 2: Compiler assets (dans nouveau terminal)
npm run dev
```

**Ouvrir navigateur:** http://localhost:8000

Vous devriez voir la page d'accueil Laravel !

---

## 🎨 Installer Tailwind CSS

### Étape 1: Installer via NPM

```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

### Étape 2: Configurer Tailwind

**Éditer `tailwind.config.js`:**
```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### Étape 3: Ajouter directives CSS

**Éditer `resources/css/app.css`:**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### Étape 4: Compiler

```bash
npm run dev
```

**Tester dans une vue:**
```html
<h1 class="text-3xl font-bold text-blue-600">
    Ça marche !
</h1>
```

---

## 🏗️ Créer Première Fonctionnalité: Gestion Salles

### Étape 1: Créer Migration

```bash
php artisan make:migration create_rooms_table
```

**Éditer `database/migrations/xxxx_create_rooms_table.php`:**
```php
public function up()
{
    Schema::create('rooms', function (Blueprint $table) {
        $table->id();
        $table->string('name', 50)->unique();
        $table->string('code', 20)->nullable()->unique();
        $table->integer('capacity')->default(1);
        $table->string('floor', 20)->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
```

```bash
php artisan migrate
```

### Étape 2: Créer Model

```bash
php artisan make:model Room
```

**Éditer `app/Models/Room.php`:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'code',
        'capacity',
        'floor',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

### Étape 3: Créer Controller

```bash
php artisan make:controller RoomController --resource
```

**Éditer `app/Http/Controllers/RoomController.php`:**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::orderBy('name')->get();
        return view('rooms.index', compact('rooms'));
    }
    
    public function create()
    {
        return view('rooms.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:rooms',
            'code' => 'nullable|string|max:20|unique:rooms',
            'capacity' => 'required|integer|min:1',
            'floor' => 'nullable|string|max:20',
        ]);
        
        Room::create($validated);
        
        return redirect()->route('rooms.index')
            ->with('success', 'Salle créée avec succès');
    }
    
    public function show(Room $room)
    {
        return view('rooms.show', compact('room'));
    }
    
    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }
    
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:rooms,name,' . $room->id,
            'code' => 'nullable|string|max:20|unique:rooms,code,' . $room->id,
            'capacity' => 'required|integer|min:1',
            'floor' => 'nullable|string|max:20',
        ]);
        
        $room->update($validated);
        
        return redirect()->route('rooms.index')
            ->with('success', 'Salle mise à jour');
    }
    
    public function destroy(Room $room)
    {
        $room->delete();
        
        return redirect()->route('rooms.index')
            ->with('success', 'Salle supprimée');
    }
}
```

### Étape 4: Créer Routes

**Éditer `routes/web.php`:**
```php
<?php

use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentification (ajouté par Breeze)
require __DIR__.'/auth.php';

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // CRUD Salles
    Route::resource('rooms', RoomController::class);
});
```

### Étape 5: Créer Vues

**Créer dossier:** `resources/views/rooms/`

**Fichier `resources/views/rooms/index.blade.php`:**
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestion des Salles
            </h2>
            <a href="{{ route('rooms.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Nouvelle Salle
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Nom</th>
                                <th class="px-4 py-2 text-left">Code</th>
                                <th class="px-4 py-2 text-left">Capacité</th>
                                <th class="px-4 py-2 text-left">Étage</th>
                                <th class="px-4 py-2 text-left">Statut</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rooms as $room)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $room->name }}</td>
                                    <td class="px-4 py-3">{{ $room->code }}</td>
                                    <td class="px-4 py-3">{{ $room->capacity }}</td>
                                    <td class="px-4 py-3">{{ $room->floor ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if($room->is_active)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('rooms.edit', $room) }}" 
                                           class="text-blue-600 hover:underline mr-3">
                                            Modifier
                                        </a>
                                        <form action="{{ route('rooms.destroy', $room) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Supprimer cette salle ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">
                                                Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        Aucune salle enregistrée
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

**Fichier `resources/views/rooms/create.blade.php`:**
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nouvelle Salle
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('rooms.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">
                                Nom de la salle *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   class="w-full border rounded px-3 py-2"
                                   required>
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">
                                Code
                            </label>
                            <input type="text" 
                                   name="code" 
                                   value="{{ old('code') }}"
                                   class="w-full border rounded px-3 py-2">
                            @error('code')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">
                                Capacité *
                            </label>
                            <input type="number" 
                                   name="capacity" 
                                   value="{{ old('capacity', 1) }}"
                                   min="1"
                                   class="w-full border rounded px-3 py-2"
                                   required>
                            @error('capacity')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-bold mb-2">
                                Étage
                            </label>
                            <input type="text" 
                                   name="floor" 
                                   value="{{ old('floor') }}"
                                   class="w-full border rounded px-3 py-2">
                            @error('floor')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                                Créer
                            </button>
                            <a href="{{ route('rooms.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 px-6 py-2 rounded">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### Étape 6: Tester !

1. **Connectez-vous:** http://localhost:8000/login
   - Email: admin@test.fr
   - Password: password123

2. **Accéder aux salles:** http://localhost:8000/rooms

3. **Créer une salle:**
   - Cliquer "Nouvelle Salle"
   - Remplir formulaire
   - Cliquer "Créer"

**Félicitations !** Vous avez créé votre premier module CRUD complet !

---

## 🔧 Commandes Utiles Laravel

```bash
# Serveur développement
php artisan serve

# Créer migration
php artisan make:migration create_xxx_table

# Exécuter migrations
php artisan migrate

# Rollback dernière migration
php artisan migrate:rollback

# Créer model
php artisan make:model NomModel

# Créer controller
php artisan make:controller NomController

# Créer controller CRUD complet
php artisan make:controller NomController --resource

# Créer tout (Model + Migration + Controller)
php artisan make:model NomModel -mcr

# Lister routes
php artisan route:list

# Console interactive (tester code)
php artisan tinker

# Vider cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Voir logs
tail -f storage/logs/laravel.log
```

---

## 🐛 Résolution Problèmes Courants

### Erreur "SQLSTATE[HY000] [2002] Connection refused"

**Problème:** MySQL n'est pas démarré

**Solution:**
```bash
# Windows (XAMPP)
Démarrer Apache + MySQL dans XAMPP Control Panel

# Mac
brew services start mysql

# Linux
sudo service mysql start
```

### Erreur "Class 'App\Models\Room' not found"

**Problème:** Fichier pas dans bon dossier

**Solution:** Vérifier que `Room.php` est dans `app/Models/`

### Erreur "Target class [RoomController] does not exist"

**Problème:** Namespace incorrect dans routes

**Solution:**
```php
// Ajouter en haut de routes/web.php
use App\Http\Controllers\RoomController;
```

### Page blanche sans erreur

**Problème:** Erreur PHP silencieuse

**Solution:**
```bash
# Vérifier logs
tail -f storage/logs/laravel.log

# Activer debug dans .env
APP_DEBUG=true
```

### CSS Tailwind ne marche pas

**Problème:** Assets pas compilés

**Solution:**
```bash
# Recompiler
npm run dev

# Vérifier que `npm run dev` tourne en arrière-plan
```

---

## 📚 Prochaines Étapes

Maintenant que vous avez l'environnement fonctionnel:

1. **Suivre tutoriel Laravel Bootcamp** (officiel):
   https://bootcamp.laravel.com/

2. **Créer les autres tables** (sessions, staff, etc.)
   - Référence: `docs/architecture/SCHEMA_BASE_DONNEES.md`

3. **Installer FullCalendar** pour le planning:
   ```bash
   npm install @fullcalendar/core @fullcalendar/daygrid
   ```

4. **Ajouter Alpine.js** pour interactivité:
   ```bash
   npm install alpinejs
   ```

---

## ✅ Checklist Installation

- [ ] PHP 8.3+ installé
- [ ] Composer installé
- [ ] MySQL 8.0 installé
- [ ] Node.js 20+ installé
- [ ] Projet Laravel créé
- [ ] Base de données créée
- [ ] `.env` configuré
- [ ] Migrations exécutées
- [ ] Utilisateur test créé
- [ ] Serveur lancé (`php artisan serve`)
- [ ] Assets compilés (`npm run dev`)
- [ ] Page d'accueil accessible
- [ ] Login fonctionnel
- [ ] Module Rooms CRUD créé et testé

---

## 🆘 Besoin d'Aide ?

**Documentation Laravel (français):**
https://laravel.sillo.org/

**Forum Laravel.io:**
https://laravel.io/forum

**Discord Laravel France:**
https://discord.gg/laravel-france

**Stack Overflow:**
Tag `[laravel]`

---

**Vous êtes prêt à développer ! 🚀**