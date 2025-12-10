# Installation de DispoDialyse

## Méthode Universelle : Docker (Recommandé)

Une seule méthode d'installation qui fonctionne sur tous les systèmes.

### Prérequis

- **Docker Desktop** (Windows/Mac) ou **Docker Engine** (Linux)
  - Windows : [Télécharger Docker Desktop](https://www.docker.com/products/docker-desktop/)
  - macOS : [Télécharger Docker Desktop](https://www.docker.com/products/docker-desktop/)
  - Linux : [Installer Docker Engine](https://docs.docker.com/engine/install/)
- **Git** : [Installer Git](https://git-scm.com/downloads)

### Installation Rapide

#### 1. Cloner le Projet

```bash
git clone https://github.com/votre-compte/DispoDialyse.git
cd DispoDialyse
```

#### 2. Lancer Docker Compose

```bash
docker-compose up -d
```

Cette commande va :
- Construire l'image Docker pour Laravel
- Démarrer MySQL 8.0
- Démarrer Redis 7
- Démarrer phpMyAdmin
- Créer les réseaux et volumes nécessaires

#### 3. Initialiser l'Application

```bash
docker-compose exec app bash .devcontainer/init.sh
```

Ce script va automatiquement :
- ✅ Installer les dépendances Composer
- ✅ Installer les dépendances NPM
- ✅ Créer le fichier `.env` depuis `.env.example`
- ✅ Générer la clé d'application Laravel
- ✅ Exécuter les migrations de base de données
- ✅ Insérer les données de test (seeders)
- ✅ Créer le lien symbolique pour le storage
- ✅ Compiler les assets frontend

#### 4. Accéder à l'Application

**Application** : http://localhost:8000

**phpMyAdmin** : http://localhost:8080
- Utilisateur : `sail`
- Mot de passe : `password`

### Comptes de Test

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@dispodialyse.fr | Password123! | Super Admin |
| medecin@dispodialyse.fr | Password123! | Médecin |
| infirmier@dispodialyse.fr | Password123! | Infirmier |
| cadre@dispodialyse.fr | Password123! | Cadre |

⚠️ **Important** : Changez ces mots de passe avant de déployer en production !

## Installation avec Make (Optionnel)

Si vous avez `make` installé, vous pouvez utiliser ces commandes simplifiées :

```bash
# Installation complète
make init

# Autres commandes utiles
make help              # Voir toutes les commandes
make up                # Démarrer les conteneurs
make down              # Arrêter les conteneurs
make logs              # Voir les logs
make shell             # Accéder au shell du conteneur
make test              # Exécuter les tests
make fresh             # Réinitialiser la base de données
make restart           # Redémarrer les conteneurs
make clean             # Nettoyer complètement
```

## Commandes Docker Compose Utiles

### Gestion des Conteneurs

```bash
# Voir l'état des conteneurs
docker-compose ps

# Voir les logs en temps réel
docker-compose logs -f app

# Arrêter tous les conteneurs
docker-compose down

# Redémarrer un service spécifique
docker-compose restart app

# Reconstruire les images
docker-compose build --no-cache
```

### Accès au Conteneur

```bash
# Accéder au shell du conteneur app
docker-compose exec app bash

# Exécuter une commande dans le conteneur
docker-compose exec app php artisan migrate
docker-compose exec app php artisan test
docker-compose exec app composer install
```

### Gestion de la Base de Données

```bash
# Exécuter les migrations
docker-compose exec app php artisan migrate

# Réinitialiser la base de données
docker-compose exec app php artisan migrate:fresh --seed

# Accéder à MySQL directement
docker-compose exec mysql mysql -u sail -ppassword dispodialyse
```

### Développement Frontend

```bash
# Compiler les assets en mode développement
docker-compose exec app npm run dev

# Compiler pour production
docker-compose exec app npm run build

# Installer de nouvelles dépendances NPM
docker-compose exec app npm install nom-du-package
```

## Environnements Supportés

### ✅ GitHub Codespaces

L'application se configure automatiquement dans Codespaces :

1. Cliquez sur **"Code"** → **"Create codespace on main"**
2. Attendez l'initialisation automatique (2-3 minutes)
3. L'application démarre automatiquement sur le port 8000

### ✅ Windows

**Prérequis** :
- Windows 10/11 avec WSL 2 activé
- Docker Desktop pour Windows

**Installation** :
1. Installer Docker Desktop et activer WSL 2
2. Ouvrir PowerShell ou Git Bash
3. Suivre les étapes d'installation ci-dessus

### ✅ macOS

**Prérequis** :
- macOS 10.15 ou supérieur
- Docker Desktop pour Mac

**Installation** :
1. Installer Docker Desktop
2. Ouvrir Terminal
3. Suivre les étapes d'installation ci-dessus

### ✅ Linux

**Prérequis** :
- Docker Engine installé
- Docker Compose installé

**Installation** :
1. Installer Docker et Docker Compose
2. Ajouter votre utilisateur au groupe docker : `sudo usermod -aG docker $USER`
3. Suivre les étapes d'installation ci-dessus

## Résolution des Problèmes

### Le port 8000 est déjà utilisé

```bash
# Modifier le port dans docker-compose.yml
# ou définir la variable d'environnement
export APP_PORT=8001
docker-compose up -d
```

### MySQL ne démarre pas

```bash
# Vérifier les logs
docker-compose logs mysql

# Supprimer les volumes et recommencer
docker-compose down -v
docker-compose up -d
```

### Permissions sur les fichiers

```bash
# Si vous avez des problèmes de permissions
docker-compose exec app chown -R sail:sail /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Réinitialisation Complète

Si vous rencontrez des problèmes, réinitialisez complètement :

```bash
# Arrêter et supprimer tout
docker-compose down -v
docker system prune -a

# Redémarrer l'installation
docker-compose up -d
docker-compose exec app bash .devcontainer/init.sh
```

## Configuration Avancée

### Variables d'Environnement

Créez un fichier `.env` à la racine pour personnaliser la configuration :

```env
# Ports personnalisés
APP_PORT=8000
DB_PORT=3306
PMA_PORT=8080
REDIS_PORT=6379

# Base de données
DB_DATABASE=dispodialyse
DB_USERNAME=sail
DB_PASSWORD=password
DB_ROOT_PASSWORD=root

# Application
APP_ENV=local
APP_DEBUG=true
```

### Mode Production

Pour activer le worker de queues en production :

```bash
# Lancer avec le profile production
docker-compose --profile production up -d

# Optimiser l'application
make optimize
```

## Support

Pour toute question ou problème :

- 📧 Email : support@dispodialyse.fr
- 📖 Documentation : [README.md](../README.md)
- 🐛 Issues : [GitHub Issues](https://github.com/votre-org/dispodialyse/issues)

## C'est Tout !

Même procédure partout : **3 commandes, ça marche !** 🚀