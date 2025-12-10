#!/bin/bash

set -e

echo "🚀 Installation de DispoDialyse pour GitHub Codespaces..."
echo ""

# Installer Composer
echo "📦 Installation de Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --quiet
rm composer-setup.php
sudo mv composer.phar /usr/local/bin/composer

# Vérifier les versions
echo ""
echo "✅ Versions installées :"
php -v | head -n 1
composer --version
node -v
npm -v

# Installer les dépendances PHP
echo ""
echo "📦 Installation des dépendances PHP (cela peut prendre quelques minutes)..."
composer install --no-interaction --prefer-dist --optimize-autoloader --quiet

# Installer les dépendances Node
echo ""
echo "📦 Installation des dépendances Node.js..."
npm install --silent

# Configuration .env pour Codespaces (SQLite)
echo ""
echo "⚙️  Configuration de l'environnement..."
if [ -f .env.codespaces ]; then
    cp .env.codespaces .env
    echo "✅ Fichier .env créé depuis .env.codespaces"
else
    cp .env.example .env
    echo "⚠️  .env.codespaces introuvable, utilisation de .env.example"
fi

# Générer la clé d'application
php artisan key:generate --force --quiet

# Créer la base de données SQLite
echo ""
echo "🗄️  Création de la base de données SQLite..."
touch database/database.sqlite
chmod 664 database/database.sqlite

# Mettre à jour la configuration pour SQLite
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_HOST=127.0.0.1/#DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=3306/#DB_PORT=3306/' .env
sed -i 's/DB_DATABASE=dispodialyse/#DB_DATABASE=dispodialyse/' .env
sed -i 's/DB_USERNAME=root/#DB_USERNAME=root/' .env
sed -i 's/DB_PASSWORD=/#DB_PASSWORD=/' .env

# Migrations et seeders
echo ""
echo "🔄 Exécution des migrations et seeders..."
php artisan migrate --force --quiet
php artisan db:seed --force --quiet

# Lien symbolique storage
echo ""
echo "🔗 Création du lien symbolique storage..."
php artisan storage:link --quiet

# Compiler les assets
echo ""
echo "🎨 Compilation des assets..."
npm run build

# Optimisation
echo ""
echo "⚡ Optimisation de l'application..."
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet

# Configuration des permissions
echo ""
echo "🔐 Configuration des permissions..."
chmod -R 775 storage bootstrap/cache
chmod +x artisan

# Afficher les informations
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Installation terminée avec succès !"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 DispoDialyse est prêt à être utilisé !"
echo ""

# Obtenir l'URL du Codespace
if [ -n "$CODESPACE_NAME" ]; then
    CODESPACE_URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
    echo "📍 Accès à l'application :"
    echo "   ${CODESPACE_URL}"
else
    echo "📍 Accès à l'application :"
    echo "   http://localhost:8000"
fi

echo ""
echo "👤 Comptes de test disponibles :"
echo "   ┌─────────────┬──────────────────────────────┬───────────────┐"
echo "   │ Rôle        │ Email                        │ Mot de passe  │"
echo "   ├─────────────┼──────────────────────────────┼───────────────┤"
echo "   │ Super Admin │ admin@dispodialyse.fr        │ Password123!  │"
echo "   │ Médecin     │ medecin@dispodialyse.fr      │ Password123!  │"
echo "   │ Infirmier   │ infirmier@dispodialyse.fr    │ Password123!  │"
echo "   │ Cadre       │ cadre@dispodialyse.fr        │ Password123!  │"
echo "   └─────────────┴──────────────────────────────┴───────────────┘"
echo ""
echo "⚠️  N'oubliez pas de changer ces mots de passe en production !"
echo ""
echo "📚 Documentation disponible dans le dossier /docs"
echo ""
echo "🚀 Commandes utiles :"
echo "   • php artisan serve --host=0.0.0.0 --port=8000  (démarrer le serveur)"
echo "   • npm run dev                                    (mode développement avec hot reload)"
echo "   • php artisan tinker                             (console interactive)"
echo "   • php artisan route:list                         (lister les routes)"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
