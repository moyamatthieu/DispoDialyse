# DispoDialyse - Système de Gestion de Planning pour Service de Dialyse

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)
[![License](https://img.shields.io/badge/License-Proprietary-yellow.svg)]()

Système complet de gestion de planning, transmissions et coordination pour services de dialyse hospitaliers.

## 📋 Table des Matières

- [Fonctionnalités](#fonctionnalités)
- [Installation Universelle](#-installation-universelle-docker)
- [Utilisation](#-utilisation)
- [Architecture](#-architecture)
- [Documentation](#-documentation)

## ✨ Fonctionnalités

### Modules Principaux

- **📅 Planning des Salles** - Gestion complète des séances de dialyse avec détection de conflits
- **👥 Annuaire Personnel** - Profils détaillés du personnel médical et administratif
- **📝 Transmissions Patients** - Partage d'informations opérationnelles avec système d'alertes
- **🌙 Planning de Garde** - Organisation des astreintes et gardes
- **📚 Référentiel Documentaire** - Protocoles, procédures et formations
- **💬 Messagerie Interne** - Communication sécurisée entre utilisateurs

### Fonctionnalités Transverses

- 🔐 Authentification avec 2FA (Laravel Breeze)
- 🔑 Gestion des rôles (8 profils utilisateurs)
- 📊 Tableaux de bord temps réel
- 🔔 Notifications WebSocket (Laravel Reverb)
- 📱 Interface responsive (mobile-first)
- ♿ Accessibilité WCAG 2.1 AA
- 🔍 Logs d'audit complets (RGPD)

## 🚀 Installation Universelle (Docker)

**Une seule méthode d'installation qui fonctionne partout : GitHub Codespaces, Windows, macOS, Linux**

### Prérequis

- **Docker Desktop** : [Installer Docker Desktop](https://www.docker.com/products/docker-desktop/)
- **Git** : [Installer Git](https://git-scm.com/downloads)

C'est tout ! Même configuration pour tous les environnements.

### Installation en 3 Commandes

```bash
# 1. Cloner le projet
git clone https://github.com/votre-compte/DispoDialyse.git
cd DispoDialyse

# 2. Lancer Docker Compose
docker-compose up -d

# 3. Initialiser l'application
docker-compose exec app bash .devcontainer/init.sh
```

### Accès

- **Application** : http://localhost:8000
- **phpMyAdmin** : http://localhost:8080 (user: sail, password: password)

### 📋 Comptes de Test

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@dispodialyse.fr | Password123! | Super Admin |
| medecin@dispodialyse.fr | Password123! | Médecin |
| infirmier@dispodialyse.fr | Password123! | Infirmier |
| cadre@dispodialyse.fr | Password123! | Cadre |

⚠️ **Changez ces mots de passe en production !**

## 🔧 Commandes Utiles

### Avec Make (Recommandé)

```bash
make help              # Afficher l'aide
make up                # Démarrer les conteneurs
make down              # Arrêter les conteneurs
make init              # Installation complète initiale
make logs              # Voir les logs
make shell             # Accéder au shell du conteneur
make test              # Exécuter les tests
make fresh             # Réinitialiser la base de données
make restart           # Redémarrer les conteneurs
make clean             # Nettoyer complètement (supprime les volumes)
```

### Commandes Docker Compose

```bash
# Voir les logs
docker-compose logs -f app

# Accéder au conteneur
docker-compose exec app bash

# Exécuter des commandes artisan
docker-compose exec app php artisan migrate
docker-compose exec app php artisan test

# Arrêter
docker-compose down

# Redémarrer depuis zéro
docker-compose down -v
docker-compose up -d
docker-compose exec app bash .devcontainer/init.sh
```

### Développement Frontend

```bash
# Compiler les assets en mode développement
docker-compose exec app npm run dev

# Compiler pour production
docker-compose exec app npm run build
```

## 📱 Environnements Supportés

✅ **GitHub Codespaces** - Ouvrir dans Codespaces = Installation automatique  
✅ **Windows** - Docker Desktop requis  
✅ **macOS** - Docker Desktop requis  
✅ **Linux** - Docker Engine requis

**Une seule configuration Docker, partout !**

## 🏗️ Architecture

### Stack Technique

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Alpine.js 3.x, Tailwind CSS 3.x
- **Base de données**: MySQL 8.0
- **Cache/Queue**: Redis 7
- **Temps réel**: Laravel Reverb (WebSocket)
- **Build**: Vite 5.x

### Services Docker

- **app** - Application Laravel (port 8000)
- **mysql** - Base de données MySQL 8.0 (port 3306)
- **redis** - Cache et queues (port 6379)
- **phpmyadmin** - Administration MySQL (port 8080)
- **worker** - Queue worker (optionnel, profile production)

### Modèles de Données

- **User** - Comptes utilisateurs
- **Personnel** - Profils détaillés du personnel
- **Salle** - Salles de dialyse
- **Reservation** - Séances de dialyse
- **Transmission** - Transmissions patients
- **Garde** - Planning de garde
- **Document** - Documents et protocoles
- **Message** - Messagerie interne
- **AuditLog** - Logs d'audit

Voir [`docs/architecture/SCHEMA_BASE_DONNEES.md`](docs/architecture/SCHEMA_BASE_DONNEES.md) pour le schéma complet.

## 🔒 Sécurité

### Bonnes Pratiques Implémentées

- ✅ Authentification 2FA
- ✅ Protection CSRF (automatique Laravel)
- ✅ Protection XSS (Blade templates)
- ✅ Validation stricte des inputs
- ✅ Rate limiting API
- ✅ Chiffrement des données sensibles
- ✅ Sessions sécurisées (httpOnly, sameSite)
- ✅ Logs d'audit complets
- ✅ Gestion des rôles et permissions

### Checklist Sécurité Production

- [ ] Changer APP_KEY
- [ ] Désactiver APP_DEBUG
- [ ] Configurer HTTPS/TLS
- [ ] Activer FORCE_HTTPS
- [ ] Configurer les headers sécurité (CSP, HSTS)
- [ ] Mettre à jour tous les mots de passe par défaut
- [ ] Configurer les sauvegardes automatiques
- [ ] Activer les logs de sécurité
- [ ] Tester la restauration de backup

## 📚 Documentation

### Documentation Complète

- [Architecture Simplifiée](docs/architecture/ARCHITECTURE_SIMPLIFIEE.md)
- [Architecture Technique](docs/architecture/ARCHITECTURE_TECHNIQUE.md)
- [Schéma Base de Données](docs/architecture/SCHEMA_BASE_DONNEES.md)
- [Guide Démarrage Rapide](docs/architecture/GUIDE_DEMARRAGE_RAPIDE.md)

### Ressources Laravel

- [Documentation Laravel](https://laravel.com/docs/11.x)
- [Laracasts](https://laracasts.com) - Tutoriels vidéo
- [Laravel News](https://laravel-news.com) - Actualités

### Ressources Frontend

- [Alpine.js Documentation](https://alpinejs.dev)
- [Tailwind CSS Documentation](https://tailwindcss.com)
- [FullCalendar Documentation](https://fullcalendar.io/docs)

## 🧪 Tests

```bash
# Lancer tous les tests
docker-compose exec app php artisan test

# Tests avec coverage
docker-compose exec app php artisan test --coverage

# Tests spécifiques
docker-compose exec app php artisan test --filter=PlanningTest
```

## 📦 Déploiement Production

```bash
# 1. Optimiser l'application
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app npm run build
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# 2. Migrer la base de données
docker-compose exec app php artisan migrate --force

# 3. Redémarrer les services
docker-compose exec app php artisan queue:restart
docker-compose exec app php artisan cache:clear
```

### Sauvegarde

```bash
# Backup base de données
docker-compose exec app php artisan backup:run

# Backup fichiers
tar -czf backup-$(date +%Y%m%d).tar.gz storage/ public/uploads/
```

## 🤝 Support

Pour toute question ou problème:

- 📧 Email: support@dispodialyse.fr
- 📞 Téléphone: +33 (0)X XX XX XX XX
- 🐛 Issues: [GitHub Issues](https://github.com/votre-org/dispodialyse/issues)

## 📄 Licence

Copyright © 2024 DispoDialyse. Tous droits réservés.

Ce logiciel est propriétaire et confidentiel.

---

**Version**: 1.0.0  
**Dernière mise à jour**: Décembre 2024  
**Développé avec** ❤️ **pour le secteur hospitalier français**