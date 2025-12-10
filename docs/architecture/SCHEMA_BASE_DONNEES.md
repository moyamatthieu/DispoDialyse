# Schéma de Base de Données Simplifié - DispoDialyse

**Version:** 1.0  
**Date:** 2025-12-10  
**SGBD:** MySQL 8.0

---

## 🎯 Principe de Conception

**Objectif:** Base de données simple, compréhensible en 30 minutes par un développeur débutant.

**Règles:**
- Pas de sur-normalisation
- Noms de tables explicites en anglais (convention Laravel)
- Relations claires one-to-many ou many-to-many
- Pas de soft deletes partout (seulement où nécessaire)
- Timestamps automatiques Laravel (`created_at`, `updated_at`)

---

## 📊 Vue d'Ensemble

**10 tables principales:**

1. `users` - Utilisateurs (authentification)
2. `staff` - Personnel (profils détaillés)
3. `rooms` - Salles de dialyse
4. `sessions` - Séances de dialyse (cœur du système)
5. `session_staff` - Pivot sessions ↔ staff (many-to-many)
6. `transmissions` - Transmissions patients
7. `oncalls` - Planning de garde
8. `documents` - Documents/protocoles
9. `messages` - Messagerie interne
10. `audit_logs` - Logs d'audit (RGPD)

---

## 📋 Détail des Tables

### 1. Table `users` - Authentification

**Rôle:** Comptes utilisateurs avec login/password

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    
    -- Rôle système (8 rôles définis)
    role ENUM(
        'super_admin',
        'admin_fonctionnel', 
        'cadre_sante',
        'medecin',
        'infirmier',
        'aide_soignant',
        'secretariat',
        'technicien'
    ) NOT NULL DEFAULT 'infirmier',
    
    -- Sécurité
    is_active BOOLEAN DEFAULT TRUE,
    mfa_enabled BOOLEAN DEFAULT FALSE,
    mfa_secret VARCHAR(255) NULL,
    
    -- Tracking
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Exemple de données:**
```sql
INSERT INTO users VALUES 
(1, 'jdupont', 'j.dupont@hopital.fr', '$2y$10$...', 'infirmier', 1, 0, NULL, NULL, NULL, NOW(), NOW()),
(2, 'mmartin', 'm.martin@hopital.fr', '$2y$10$...', 'medecin', 1, 1, 'secret2FA', NULL, NULL, NOW(), NOW());
```

---

### 2. Table `staff` - Personnel (Profils Détaillés)

**Rôle:** Informations professionnelles complètes (annuaire)

```sql
CREATE TABLE staff (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NULL, -- NULL si personnel externe sans compte
    
    -- Identité
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    photo_url VARCHAR(500) NULL,
    
    -- Contact
    phone_office VARCHAR(20) NULL,
    phone_mobile VARCHAR(20) NULL,
    phone_pager VARCHAR(20) NULL,
    email_pro VARCHAR(255) NULL,
    extension VARCHAR(10) NULL, -- Numéro poste
    
    -- Professionnel
    job_title VARCHAR(150) NOT NULL, -- Ex: "Infirmier Diplômé d'État"
    specialty VARCHAR(100) NULL, -- Ex: "Néphrologie"
    department VARCHAR(100) DEFAULT 'Dialyse',
    employment_type ENUM('full_time', 'part_time', 'contractor') DEFAULT 'full_time',
    
    -- Compétences
    qualifications TEXT NULL, -- JSON: ["Dialyse péritonéale", "Hémodialyse"]
    languages TEXT NULL, -- JSON: ["Français", "Anglais", "Arabe"]
    certifications TEXT NULL, -- JSON array
    
    -- Disponibilité
    is_active BOOLEAN DEFAULT TRUE,
    hire_date DATE NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_name (last_name, first_name),
    INDEX idx_active (is_active),
    FULLTEXT idx_search (first_name, last_name, job_title, specialty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Pourquoi `user_id` nullable?** 
- Personnel externe (stagiaires, remplaçants) sans compte système
- Séparation authentification (users) et données RH (staff)

---

### 3. Table `rooms` - Salles de Dialyse

**Rôle:** Configuration des salles

```sql
CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identification
    name VARCHAR(50) NOT NULL UNIQUE, -- Ex: "Salle A1", "Box 3"
    code VARCHAR(20) NULL UNIQUE, -- Ex: "SA1", "B03"
    
    -- Caractéristiques
    capacity INT DEFAULT 1, -- Nombre de postes
    floor VARCHAR(20) NULL, -- Étage
    building VARCHAR(50) NULL, -- Bâtiment
    
    -- Équipements (JSON array pour simplicité)
    equipment TEXT NULL, -- JSON: ["Générateur Fresenius 5008", "Chaise électrique"]
    
    -- Configuration
    is_active BOOLEAN DEFAULT TRUE,
    is_isolation BOOLEAN DEFAULT FALSE, -- Salle d'isolement
    
    -- Métadonnées
    notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_active (is_active),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Exemple:**
```sql
INSERT INTO rooms VALUES 
(1, 'Salle A - Poste 1', 'SA1', 1, 'RDC', 'Principal', '["Fresenius 5008", "Balance"]', 1, 0, NULL, NOW(), NOW()),
(2, 'Box Isolement', 'ISO1', 1, 'RDC', 'Principal', '["Fresenius 5008", "Monitoring"]', 1, 1, 'Patients VHC/VHB', NOW(), NOW());
```

---

### 4. Table `sessions` - Séances de Dialyse (CŒUR)

**Rôle:** Planning des séances

```sql
CREATE TABLE sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Salle
    room_id BIGINT UNSIGNED NOT NULL,
    
    -- Horaires
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    
    -- Patient (anonymisé)
    patient_reference VARCHAR(50) NULL, -- Ex: "PAT-2024-001" ou code interne
    patient_initials VARCHAR(10) NULL, -- Optionnel: "J.D."
    
    -- Type dialyse
    dialysis_type ENUM(
        'hemodialysis',           -- Hémodialyse
        'hemodiafiltration',      -- Hémodiafiltration
        'peritoneal',             -- Dialyse péritonéale
        'hemofiltration'          -- Hémofiltration
    ) NOT NULL DEFAULT 'hemodialysis',
    
    -- Statut
    status ENUM(
        'scheduled',   -- Planifiée
        'in_progress', -- En cours
        'completed',   -- Terminée
        'cancelled',   -- Annulée
        'no_show'      -- Patient absent
    ) DEFAULT 'scheduled',
    
    -- Informations opérationnelles
    notes TEXT NULL,
    special_requirements TEXT NULL, -- Précautions, isolement, etc.
    
    -- Métadonnées
    created_by BIGINT UNSIGNED NULL,
    cancelled_at DATETIME NULL,
    cancellation_reason TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Index pour performance
    INDEX idx_room_time (room_id, start_time, end_time),
    INDEX idx_start_time (start_time),
    INDEX idx_status (status),
    INDEX idx_patient (patient_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Détection conflits:**
```sql
-- Requête pour vérifier conflit lors de création/modification
SELECT COUNT(*) FROM sessions
WHERE room_id = ?
  AND status NOT IN ('cancelled', 'completed')
  AND (
    (start_time BETWEEN ? AND ?) OR
    (end_time BETWEEN ? AND ?) OR
    (? BETWEEN start_time AND end_time)
  );
```

---

### 5. Table `session_staff` - Pivot Sessions ↔ Staff

**Rôle:** Assigner personnel aux séances (many-to-many)

```sql
CREATE TABLE session_staff (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    staff_id BIGINT UNSIGNED NOT NULL,
    
    -- Rôle dans la séance
    role_in_session ENUM('lead_nurse', 'assistant_nurse', 'aide_soignant', 'physician') 
        DEFAULT 'assistant_nurse',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_session_staff (session_id, staff_id),
    INDEX idx_session (session_id),
    INDEX idx_staff (staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Utilisation Laravel:**
```php
// Assigner staff à une session
$session->staff()->attach([1, 2, 3]);

// Récupérer staff d'une session
$staff = $session->staff; // Collection

// Récupérer sessions d'un staff
$sessions = $staff->sessions;
```

---

### 6. Table `transmissions` - Transmissions Patients

**Rôle:** Partage d'informations opérationnelles patients

```sql
CREATE TABLE transmissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Patient (lié à une session optionnellement)
    session_id BIGINT UNSIGNED NULL,
    patient_reference VARCHAR(50) NOT NULL,
    
    -- Classification
    category ENUM('logistique', 'comportement', 'clinique', 'precaution') NOT NULL,
    priority ENUM('normale', 'importante', 'urgente') DEFAULT 'normale',
    
    -- Contenu
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    
    -- Données cliniques (JSON pour flexibilité)
    vital_signs JSON NULL, -- {"blood_pressure_sys": 120, "blood_pressure_dia": 80, ...}
    
    -- Alertes (si seuils dépassés)
    has_alert BOOLEAN DEFAULT FALSE,
    alert_acknowledged BOOLEAN DEFAULT FALSE,
    alert_acknowledged_by BIGINT UNSIGNED NULL,
    alert_acknowledged_at DATETIME NULL,
    
    -- Auteur
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Visibilité
    is_archived BOOLEAN DEFAULT FALSE,
    archived_at DATETIME NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (alert_acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_patient (patient_reference),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    INDEX idx_alerts (has_alert, alert_acknowledged),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Exemple JSON vital_signs:**
```json
{
  "blood_pressure_systolic": 120,
  "blood_pressure_diastolic": 80,
  "heart_rate": 75,
  "temperature": 36.8,
  "pain_score": 2,
  "recorded_at": "2024-01-15T14:30:00Z"
}
```

---

### 7. Table `oncalls` - Planning de Garde

**Rôle:** Plannings de garde et astreintes

```sql
CREATE TABLE oncalls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Personnel de garde
    staff_id BIGINT UNSIGNED NOT NULL,
    
    -- Période
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    
    -- Type de garde
    oncall_type ENUM(
        'day_shift',      -- Garde jour
        'night_shift',    -- Garde nuit
        'weekend',        -- Week-end
        'holiday',        -- Jour férié
        'on_call'         -- Astreinte
    ) NOT NULL,
    
    -- Catégorie
    category ENUM('medical', 'nursing', 'technical') DEFAULT 'nursing',
    
    -- Statut
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled') DEFAULT 'scheduled',
    
    -- Métadonnées
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_staff (staff_id),
    INDEX idx_period (start_datetime, end_datetime),
    INDEX idx_type (oncall_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Requête "Qui est de garde maintenant?":**
```sql
SELECT s.* FROM staff s
INNER JOIN oncalls o ON s.id = o.staff_id
WHERE NOW() BETWEEN o.start_datetime AND o.end_datetime
  AND o.status = 'confirmed';
```

---

### 8. Table `documents` - Référentiel Documentaire

**Rôle:** Protocoles, procédures, formations

```sql
CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identification
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    -- Catégorisation
    category ENUM(
        'protocol',       -- Protocole de soins
        'procedure',      -- Procédure organisationnelle
        'technical',      -- Fiche technique
        'training',       -- Formation
        'regulation',     -- Réglementation
        'contact',        -- Contacts utiles
        'practical'       -- Infos pratiques
    ) NOT NULL,
    
    -- Fichier
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NULL, -- Ex: "application/pdf"
    file_size INT NULL, -- Octets
    
    -- Version
    version VARCHAR(20) DEFAULT '1.0',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    
    -- Métadonnées
    tags TEXT NULL, -- JSON array: ["dialyse", "urgence", "protocole"]
    author VARCHAR(100) NULL,
    published_at DATE NULL,
    expires_at DATE NULL, -- Pour documents périssables
    
    -- Permissions
    restricted_to_roles TEXT NULL, -- JSON array de rôles autorisés, NULL = tous
    
    -- Tracking
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_published (published_at),
    FULLTEXT idx_search (title, description, author)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 9. Table `messages` - Messagerie Interne

**Rôle:** Communication entre utilisateurs

```sql
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Expéditeur/Destinataire
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    
    -- Contenu
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    
    -- Statut
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    
    -- Thread (conversation)
    parent_message_id BIGINT UNSIGNED NULL, -- NULL si message initial
    
    -- Suppression (soft delete pour RGPD)
    deleted_by_sender BOOLEAN DEFAULT FALSE,
    deleted_by_recipient BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE SET NULL,
    
    INDEX idx_recipient (recipient_id, is_read),
    INDEX idx_sender (sender_id),
    INDEX idx_thread (parent_message_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Soft Delete:**
- Message supprimé par expéditeur: `deleted_by_sender = 1`
- Message supprimé par destinataire: `deleted_by_recipient = 1`
- Si les deux = 1, purge physique après 90 jours (RGPD)

---

### 10. Table `audit_logs` - Logs d'Audit (RGPD/Sécurité)

**Rôle:** Traçabilité complète des actions

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Qui?
    user_id BIGINT UNSIGNED NULL, -- NULL si action système
    ip_address VARCHAR(45) NULL, -- Support IPv6
    user_agent TEXT NULL,
    
    -- Quoi?
    action VARCHAR(100) NOT NULL, -- Ex: "sessions.create", "users.login", "documents.download"
    entity_type VARCHAR(50) NULL, -- Ex: "Session", "User"
    entity_id BIGINT UNSIGNED NULL,
    
    -- Détails
    description TEXT NULL,
    changes JSON NULL, -- {"old": {...}, "new": {...}}
    
    -- Métadonnées
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at),
    INDEX idx_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Exemple d'entrée:**
```json
{
  "user_id": 5,
  "ip_address": "192.168.1.100",
  "action": "sessions.update",
  "entity_type": "Session",
  "entity_id": 123,
  "description": "Modification horaires session",
  "changes": {
    "old": {"start_time": "2024-01-15 14:00:00"},
    "new": {"start_time": "2024-01-15 15:00:00"}
  },
  "severity": "info"
}
```

**Rétention:** 2 ans minimum (obligation légale santé)

---

## 📊 Diagramme Relationnel (ERD)

```
┌─────────────────┐
│     USERS       │
│─────────────────│
│ id (PK)         │
│ username        │◄──────────┐
│ email           │           │
│ password        │           │
│ role            │           │
│ mfa_enabled     │           │
└─────────────────┘           │
        │                     │
        │ 1:1                 │
        │                     │
┌───────▼─────────┐           │
│     STAFF       │           │
│─────────────────│           │
│ id (PK)         │           │
│ user_id (FK)    │           │
│ first_name      │           │
│ last_name       │           │
│ phone_mobile    │           │
│ job_title       │           │
└─────────────────┘           │
        │                     │
        │ 1:N                 │
        │                     │
┌───────▼──────────────┐      │
│   SESSION_STAFF      │      │
│──────────────────────│      │
│ id (PK)              │      │
│ session_id (FK) ─────┼──┐   │
│ staff_id (FK)        │  │   │
│ role_in_session      │  │   │
└──────────────────────┘  │   │
                          │   │
        ┌─────────────────┘   │
        │                     │
┌───────▼─────────┐           │
│    SESSIONS     │           │
│─────────────────│           │
│ id (PK)         │           │
│ room_id (FK) ───┼───┐       │
│ start_time      │   │       │
│ end_time        │   │       │
│ patient_ref     │   │       │
│ dialysis_type   │   │       │
│ status          │   │       │
│ created_by (FK) ├───┼───────┘
└─────────────────┘   │
                      │
        ┌─────────────┘
        │
┌───────▼─────────┐
│     ROOMS       │
│─────────────────│
│ id (PK)         │
│ name            │
│ capacity        │
│ is_isolation    │
└─────────────────┘

┌──────────────────┐         ┌─────────────────┐
│  TRANSMISSIONS   │         │    ONCALLS      │
│──────────────────│         │─────────────────│
│ id (PK)          │         │ id (PK)         │
│ session_id (FK) ─┼─────┐   │ staff_id (FK) ──┼───┐
│ patient_ref      │     │   │ start_datetime  │   │
│ category         │     │   │ end_datetime    │   │
│ priority         │     │   │ oncall_type     │   │
│ content          │     │   └─────────────────┘   │
│ vital_signs      │     │                         │
│ has_alert        │     │                         │
│ created_by (FK) ─┼───┐ │   ┌─────────────────┐   │
└──────────────────┘   │ │   │   DOCUMENTS     │   │
                       │ │   │─────────────────│   │
┌──────────────────┐   │ │   │ id (PK)         │   │
│    MESSAGES      │   │ │   │ title           │   │
│──────────────────│   │ │   │ category        │   │
│ id (PK)          │   │ └───│ file_path       │   │
│ sender_id (FK) ──┼───┼─────│ status          │   │
│ recipient_id(FK)─┼───┘     │ created_by (FK)─┼───┘
│ subject          │         └─────────────────┘
│ body             │
│ is_read          │         ┌─────────────────┐
└──────────────────┘         │   AUDIT_LOGS    │
                             │─────────────────│
                             │ id (PK)         │
                             │ user_id (FK)────┼───┐
                             │ action          │   │
                             │ entity_type     │   │
                             │ entity_id       │   │
                             │ changes (JSON)  │   │
                             └─────────────────┘   │
                                                   │
                                    Toutes tables──┘
```

---

## 🔐 Sécurité Base de Données

### Utilisateurs MySQL

```sql
-- Utilisateur application (lecture/écriture limitée)
CREATE USER 'dispodialyse_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON dispodialyse.* TO 'dispodialyse_app'@'localhost';

-- Utilisateur backup (lecture seule)
CREATE USER 'dispodialyse_backup'@'localhost' IDENTIFIED BY 'backup_password';
GRANT SELECT, LOCK TABLES ON dispodialyse.* TO 'dispodialyse_backup'@'localhost';

-- Admin (pour migrations)
CREATE USER 'dispodialyse_admin'@'localhost' IDENTIFIED BY 'admin_password';
GRANT ALL PRIVILEGES ON dispodialyse.* TO 'dispodialyse_admin'@'localhost';

FLUSH PRIVILEGES;
```

### Chiffrement Données Sensibles

**Laravel Encryption (champs critiques):**
```php
// Dans Model
protected $casts = [
    'mfa_secret' => 'encrypted', // Automatiquement chiffré/déchiffré
    'vital_signs' => 'encrypted:json'
];
```

### Backup Automatisé

**Script mysqldump quotidien:**
```bash
#!/bin/bash
# /home/backup/backup-db.sh

DB_NAME="dispodialyse"
DB_USER="dispodialyse_backup"
DB_PASS="backup_password"
BACKUP_DIR="/home/backup/mysql"
DATE=$(date +%Y%m%d_%H%M%S)

# Dump base de données
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Garder 30 jours
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

# Copier sur serveur distant (optionnel)
rsync -avz $BACKUP_DIR/db_$DATE.sql.gz backup-server:/backups/
```

**Crontab:**
```bash
# Backup quotidien 2h du matin
0 2 * * * /home/backup/backup-db.sh
```

---

## 🚀 Migrations Laravel

### Structure Migrations

```
database/migrations/
├── 2024_01_01_000001_create_users_table.php
├── 2024_01_01_000002_create_staff_table.php
├── 2024_01_01_000003_create_rooms_table.php
├── 2024_01_01_000004_create_sessions_table.php
├── 2024_01_01_000005_create_session_staff_table.php
├── 2024_01_01_000006_create_transmissions_table.php
├── 2024_01_01_000007_create_oncalls_table.php
├── 2024_01_01_000008_create_documents_table.php
├── 2024_01_01_000009_create_messages_table.php
└── 2024_01_01_000010_create_audit_logs_table.php
```

### Exemple Migration Complète

```php
// database/migrations/2024_01_01_000004_create_sessions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            
            $table->string('patient_reference', 50)->nullable();
            $table->string('patient_initials', 10)->nullable();
            
            $table->enum('dialysis_type', [
                'hemodialysis',
                'hemodiafiltration',
                'peritoneal',
                'hemofiltration'
            ])->default('hemodialysis');
            
            $table->enum('status', [
                'scheduled',
                'in_progress',
                'completed',
                'cancelled',
                'no_show'
            ])->default('scheduled');
            
            $table->text('notes')->nullable();
            $table->text('special_requirements')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['room_id', 'start_time', 'end_time']);
            $table->index('start_time');
            $table->index('status');
            $table->index('patient_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
```

### Commandes Migration

```bash
# Créer toutes les tables
php artisan migrate

# Rollback dernière migration
php artisan migrate:rollback

# Réinitialiser complètement (DEV seulement!)
php artisan migrate:fresh

# Voir statut migrations
php artisan migrate:status
```

---

## 📊 Données de Test (Seeders)

```php
// database/seeders/DatabaseSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,        // Créer users admin
            StaffSeeder::class,       // Créer 20 staff fictifs
            RoomSeeder::class,        // Créer 10 salles
            SessionSeeder::class,     // Créer 50 sessions test
            DocumentSeeder::class,    // Créer 10 documents
        ]);
    }
}
```

```bash
# Insérer données de test
php artisan db:seed

# Réinitialiser + seed (DEV)
php artisan migrate:fresh --seed
```

---

## 📈 Optimisations Performances

### Index Essentiels

**Déjà ajoutés dans schéma:**
- Index sur foreign keys (automatique)
- Index sur colonnes de recherche (name, username, email)
- Index composites pour requêtes fréquentes (room_id + start_time)
- FULLTEXT index pour recherche textuelle

### Requêtes Optimisées

**Éviter N+1:**
```php
// ❌ MAL (N+1 queries)
$sessions = Session::all();
foreach($sessions as $session) {
    echo $session->room->name; // Query pour chaque room
}

// ✅ BIEN (2 queries seulement)
$sessions = Session::with('room')->get();
foreach($sessions as $session) {
    echo $session->room->name; // Déjà chargé
}
```

### Pagination

```php
// Toujours paginer les listes
$sessions = Session::latest()->paginate(20); // 20 par page
```

### Cache Requêtes Fréquentes

```php
// Cache résultat 1 heure
$rooms = Cache::remember('active_rooms', 3600, function() {
    return Room::where('is_active', true)->get();
});
```

---

## ✅ Checklist Implémentation

### Phase Setup
- [ ] Créer base de données MySQL `dispodialyse`
- [ ] Configurer `.env` avec credentials DB
- [ ] Exécuter migrations: `php artisan migrate`
- [ ] Créer utilisateur test: `php artisan db:seed --class=UserSeeder`

### Phase Test
- [ ] Insérer données de test: `php artisan db:seed`
- [ ] Vérifier relations: ouvrir phpMyAdmin
- [ ] Tester requêtes dans Tinker: `php artisan tinker`

### Phase Production
- [ ] Backup initial base vide
- [ ] Import données réelles (staff, rooms)
- [ ] Configurer backups automatiques (cron)
- [ ] Activer SSL MySQL

---

## 📚 Ressources

**Documentation Laravel Migrations:**
https://laravel.com/docs/11.x/migrations

**Documentation Laravel Eloquent:**
https://laravel.com/docs/11.x/eloquent

**phpMyAdmin (interface visuelle):**
- Naviguer structure tables
- Tester requêtes SQL
- Exporter/Importer données

---

**Cette base de données est SIMPLE, COMPRÉHENSIBLE et MAINTENABLE sur le long terme.**