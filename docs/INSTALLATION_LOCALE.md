# Installation Locale de DispoDialyse

Ce guide vous accompagne dans l'installation complète de DispoDialyse sur votre machine locale (Windows, macOS ou Linux).

## 📋 Table des Matières

- [Prérequis Système](#prérequis-système)
- [Installation sur Windows](#installation-sur-windows)
- [Installation sur macOS](#installation-sur-macos)
- [Installation sur Linux](#installation-sur-linux)
- [Installation Étape par Étape](#installation-étape-par-étape)
- [Configuration de la Base de Données](#configuration-de-la-base-de-données)
- [Lancement de l'Application](#lancement-de-lapplication)
- [Comptes de Test](#comptes-de-test)
- [Dépannage](#dépannage)

## 🔧 Prérequis Système

### Logiciels Requis

| Logiciel | Version Minimale | Version Recommandée |
|----------|------------------|---------------------|
| PHP | 8.2 | 8.3+ |
| Composer | 2.5 | Dernière |
| Node.js | 18.x | 20.x LTS |
| NPM | 9.x | 10.x |
| MySQL | 8.0 | 8.0+ |
| Git | 2.x | Dernière |

### Extensions PHP Requises

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
- GD
- Zip

## 💻 Installation sur Windows

### 1. Installer PHP

**Option A : Téléchargement Manuel**

1. Télécharger PHP 8.2+ depuis [windows.php.net/download](https://windows.php.net/download/)
2. Extraire l'archive dans `C:\php`
3. Ajouter `C:\php` au PATH système
4. Copier `php.ini-development` vers `php.ini`
5. Activer les extensions requises dans `php.ini` :

```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=zip
```

**Option B : Via XAMPP (Recommandé pour débutants)**

1. Télécharger [XAMPP](https://www.apachefriends.org/)
2. Installer et démarrer Apache et MySQL via le panneau de contrôle

### 2. Installer Composer

1. Télécharger depuis [getcomposer.org](https://getcomposer.org/download/)
2. Exécuter l'installeur `Composer-Setup.exe`
3. Vérifier l'installation :

```cmd
composer --version
```

### 3. Installer Node.js

1. Télécharger depuis [nodejs.org](https://nodejs.org/)
2. Installer la version LTS
3. Vérifier l'installation :

```cmd
node -v
npm -v
```

### 4. Installer Git

1. Télécharger depuis [git-scm.com](https://git-scm.com/download/win)
2. Installer avec les options par défaut
3. Vérifier l'installation :

```cmd
git --version
```

### 5. Installer MySQL

**Si vous n'utilisez pas XAMPP :**

1. Télécharger [MySQL Installer](https://dev.mysql.com/downloads/installer/)
2. Installer MySQL Server 8.0+
3. Noter le mot de passe root défini pendant l'installation

## 🍎 Installation sur macOS

### Utiliser Homebrew (Recommandé)

1. **Installer Homebrew** (si pas déjà fait) :

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

2. **Installer tous les prérequis** :

```bash
# Installer PHP 8.2
brew install php@8.2

# Installer Composer
brew install composer

# Installer Node.js
brew install node@20

# Installer MySQL
brew install mysql

# Installer Git
brew install git
```

3. **Démarrer MySQL** :

```bash
brew services start mysql
```

4. **Sécuriser MySQL** :

```bash
mysql_secure_installation
```

5. **Vérifier les installations** :

```bash
php -v
composer --version
node -v
npm -v
mysql --version
git --version
```

## 🐧 Installation sur Linux (Ubuntu/Debian)

### 1. Mettre à jour le système

```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Installer PHP et extensions

```bash
sudo apt install -y php8.2 php8.2-{cli,fpm,mysql,xml,mbstring,curl,zip,gd,bcmath,tokenizer,fileinfo}
```

### 3. Installer Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 4. Installer Node.js et NPM

```bash
# Via NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 5. Installer MySQL

```bash
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
sudo mysql_secure_installation
```

### 6. Installer Git

```bash
sudo apt install -y git
```

### 7. Vérifier les installations

```bash
php -v
composer --version
node -v
npm -v
mysql --version
git --version
```

## 📥 Installation Étape par Étape

### 1. Cloner le Projet

```bash
git clone https://github.com/votre-compte/DispoDialyse.git
cd DispoDialyse
```

### 2. Installer les Dépendances

```bash
# Dépendances PHP
composer install

# Dépendances JavaScript
npm install
```

**Note :** L'installation peut prendre quelques minutes selon votre connexion.

## 🗄️ Configuration de la Base de Données

### 1. Créer la Base de Données MySQL

**Windows (XAMPP) :**

1. Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
2. Créer une nouvelle base de données nommée `dispodialyse`
3. Définir le jeu de caractères : `utf8mb4_unicode_ci`

**Ligne de commande (tous systèmes) :**

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base de données
CREATE DATABASE dispodialyse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Créer un utilisateur dédié (optionnel mais recommandé)
CREATE USER 'dispodialyse'@'localhost' IDENTIFIED BY 'votre_mot_de_passe_securise';
GRANT ALL PRIVILEGES ON dispodialyse.* TO 'dispodialyse'@'localhost';
FLUSH PRIVILEGES;

# Quitter MySQL
EXIT;
```

### 2. Configurer le Fichier .env

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 3. Éditer le Fichier .env

Ouvrir `.env` avec votre éditeur préféré et configurer la connexion MySQL :

```env
APP_NAME="DispoDialyse"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dispodialyse
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe

# Ou si vous avez créé un utilisateur dédié :
# DB_USERNAME=dispodialyse
# DB_PASSWORD=votre_mot_de_passe_securise
```

### 4. Exécuter les Migrations et Seeders

```bash
# Créer toutes les tables et insérer les données de test
php artisan migrate --seed
```

**Que fait cette commande ?**
- Crée toutes les tables de la base de données
- Insère les rôles et permissions
- Crée des utilisateurs de test
- Génère des données d'exemple (salles, réservations, etc.)

### 5. Créer le Lien de Stockage

```bash
php artisan storage:link
```

Cette commande crée un lien symbolique permettant l'accès public aux fichiers uploadés.

### 6. Compiler les Assets

```bash
# Pour le développement (avec hot reload)
npm run dev

# OU pour la production
npm run build
```

## 🚀 Lancement de l'Application

### Option 1 : Serveur de Développement Laravel (Recommandé)

**Dans un premier terminal :**

```bash
php artisan serve
```

L'application est accessible à : **http://localhost:8000**

**Dans un second terminal (pour le développement avec hot reload) :**

```bash
npm run dev
```

### Option 2 : Via Apache/Nginx

#### Apache (XAMPP Windows)

1. Placer le projet dans `C:\xampp\htdocs\dispodialyse`
2. Configurer un Virtual Host dans `httpd-vhosts.conf` :

```apache
<VirtualHost *:80>
    ServerName dispodialyse.local
    DocumentRoot "C:/xampp/htdocs/dispodialyse/public"
    
    <Directory "C:/xampp/htdocs/dispodialyse/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Ajouter dans `C:\Windows\System32\drivers\etc\hosts` :

```
127.0.0.1    dispodialyse.local
```

4. Redémarrer Apache
5. Accéder à : **http://dispodialyse.local**

#### Nginx (Linux/macOS)

Configuration dans `/etc/nginx/sites-available/dispodialyse` :

```nginx
server {
    listen 80;
    server_name dispodialyse.local;
    root /chemin/vers/DispoDialyse/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activer le site :

```bash
sudo ln -s /etc/nginx/sites-available/dispodialyse /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 👤 Comptes de Test

Après avoir exécuté les seeders, vous pouvez vous connecter avec ces comptes :

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| **Super Admin** | admin@dispodialyse.fr | Password123! |
| **Médecin** | medecin@dispodialyse.fr | Password123! |
| **Infirmier** | infirmier@dispodialyse.fr | Password123! |
| **Cadre de Santé** | cadre@dispodialyse.fr | Password123! |
| **Secrétaire** | secretaire@dispodialyse.fr | Password123! |

⚠️ **IMPORTANT** : Changez ces mots de passe avant toute mise en production !

## 🔧 Dépannage

### Erreur "Class not found" ou Autoload

```bash
composer dump-autoload
php artisan cache:clear
php artisan config:clear
```

### Erreur de Permissions (Linux/macOS)

```bash
# Donner les permissions nécessaires
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

### Erreur "Connection refused" MySQL

**Vérifier que MySQL est démarré :**

- **Windows XAMPP** : Démarrer MySQL dans le panneau de contrôle XAMPP
- **macOS** : `brew services start mysql`
- **Linux** : `sudo systemctl start mysql`

**Vérifier les identifiants dans `.env` :**

```bash
# Tester la connexion MySQL
mysql -u root -p dispodialyse
```

### Port 8000 déjà utilisé

```bash
# Lancer sur un autre port
php artisan serve --port=8080
```

Puis accéder à : `http://localhost:8080`

### Erreur "npm ERR! code ELIFECYCLE"

```bash
# Supprimer node_modules et réinstaller
rm -rf node_modules package-lock.json
npm install
```

### Erreur "Vite manifest not found"

```bash
# Compiler les assets
npm run build

# OU en mode dev
npm run dev
```

### SQLSTATE[HY000] [2002] Connection refused

Vérifiez que :
1. MySQL est démarré
2. Les paramètres de connexion dans `.env` sont corrects
3. La base de données existe

```bash
# Vérifier l'état de MySQL
# Linux/macOS
sudo systemctl status mysql

# Windows (dans le panneau XAMPP)
# Vérifier que MySQL est "Running"
```

### Erreur "Migration table not found"

```bash
# Réinitialiser complètement la base de données
php artisan migrate:fresh --seed
```

### Page blanche après installation

1. Vérifier les logs : `storage/logs/laravel.log`
2. Activer le mode debug dans `.env` :

```env
APP_DEBUG=true
```

3. Vérifier les permissions des dossiers `storage` et `bootstrap/cache`

## 📚 Commandes Utiles

### Gestion de la Base de Données

```bash
# Réinitialiser la base de données
php artisan migrate:fresh --seed

# Créer seulement les tables (sans données)
php artisan migrate

# Ajouter des données de test
php artisan db:seed
```

### Cache et Optimisation

```bash
# Vider tous les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Développement

```bash
# Console interactive
php artisan tinker

# Lister toutes les routes
php artisan route:list

# Lister les commandes disponibles
php artisan list
```

## 🎯 Prochaines Étapes

Une fois l'installation terminée :

1. **Explorez l'interface** : Connectez-vous avec le compte admin
2. **Consultez la documentation** : Dossier `/docs`
3. **Personnalisez** : Adaptez les données de test à vos besoins
4. **Développez** : Ajoutez vos fonctionnalités personnalisées

## 📞 Support

Pour toute assistance :

- 📧 Email : support@dispodialyse.fr
- 📚 Documentation : `/docs`
- 🐛 Problèmes : [GitHub Issues](https://github.com/votre-compte/DispoDialyse/issues)

---

**Bon développement avec DispoDialyse ! 🚀**