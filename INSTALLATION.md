# Guide d'Installation - DispoDialyse

## 📋 Prérequis

Avant de commencer l'installation, assurez-vous d'avoir les éléments suivants installés sur votre système :

- **PHP 8.2 ou supérieur** avec les extensions suivantes :
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - PDO_MySQL
  - Tokenizer
  - XML

- **Composer** (gestionnaire de dépendances PHP)
- **Node.js 18+** et **npm** (pour la compilation des assets frontend)
- **MySQL 8.0+** ou **MariaDB 10.3+**
- **Git** (pour le clonage du repository)

## 🚀 Installation

### 1. Cloner le repository

```bash
git clone https://github.com/votre-organisation/DispoDialyse.git
cd DispoDialyse
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances JavaScript

```bash
npm install
```

### 4. Configurer l'environnement

Copiez le fichier d'exemple de configuration et générez une clé d'application :

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurer la base de données

Éditez le fichier `.env` et configurez les paramètres de connexion à la base de données :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dispodialyse
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

**Créez la base de données :**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE dispodialyse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 6. Exécuter les migrations et seeders

```bash
php artisan migrate --seed
```

Cette commande va :
- Créer toutes les tables nécessaires
- Insérer les données de test (utilisateurs, personnel, salles, etc.)

### 7. Créer le lien symbolique pour le stockage

```bash
php artisan storage:link
```

### 8. Compiler les assets frontend

**Pour le développement :**

```bash
npm run dev
```

**Pour la production :**

```bash
npm run build
```

### 9. Lancer le serveur de développement

```bash
php artisan serve
```

L'application sera accessible à l'adresse : **http://localhost:8000**

## 👤 Comptes de test

Après l'exécution des seeders, vous pouvez vous connecter avec les comptes suivants :

### Administrateur
- **Email :** admin@dispodialyse.fr
- **Mot de passe :** password

### Cadre de Santé
- **Email :** cadre@dispodialyse.fr
- **Mot de passe :** password

### Infirmier
- **Email :** infirmier@dispodialyse.fr
- **Mot de passe :** password

### Médecin
- **Email :** medecin@dispodialyse.fr
- **Mot de passe :** password

## 🔧 Configuration supplémentaire

### Configuration de l'email

Pour activer l'envoi d'emails, configurez les paramètres SMTP dans le fichier `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@dispodialyse.fr
MAIL_FROM_NAME="DispoDialyse"
```

### Configuration du cache

Pour de meilleures performances, vous pouvez configurer Redis :

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Configuration des queues

Pour traiter les tâches en arrière-plan :

```bash
php artisan queue:work
```

Pour un environnement de production, utilisez Supervisor pour gérer les workers.

## 🧪 Tests

### Exécuter les tests

```bash
php artisan test
```

### Avec couverture de code

```bash
php artisan test --coverage
```

## 🔒 Sécurité

### Permissions des fichiers

Assurez-vous que les répertoires suivants sont accessibles en écriture par le serveur web :

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Configuration de production

Pour un environnement de production, modifiez les paramètres suivants dans `.env` :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Activez le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🐛 Dépannage

### Erreur "Class not found"

```bash
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
```

### Erreur de permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Erreur de migration

Si les migrations échouent, réinitialisez la base de données :

```bash
php artisan migrate:fresh --seed
```

⚠️ **Attention :** Cette commande supprimera toutes les données existantes.

### Erreur npm

Si vous rencontrez des erreurs avec npm :

```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

### Logs de l'application

Consultez les logs pour diagnostiquer les problèmes :

```bash
tail -f storage/logs/laravel.log
```

## 📚 Documentation supplémentaire

- [Architecture Technique](docs/architecture/ARCHITECTURE_TECHNIQUE.md)
- [Schéma de Base de Données](docs/architecture/SCHEMA_BASE_DONNEES.md)
- [Guide de Démarrage Rapide](docs/architecture/GUIDE_DEMARRAGE_RAPIDE.md)
- [Documentation Authentification](docs/AUTHENTIFICATION.md)
- [Documentation Planning](docs/PLANNING.md)
- [Documentation Annuaire](docs/ANNUAIRE.md)

## 🆘 Support

Pour toute question ou problème :

1. Consultez la documentation dans le dossier `docs/`
2. Vérifiez les issues GitHub existantes
3. Créez une nouvelle issue si nécessaire

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.