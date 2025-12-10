#!/bin/bash
set -e

echo "🚀 Initialisation de DispoDialyse..."

# Attendre que MySQL soit prêt
echo "⏳ Attente de MySQL..."
until docker-compose exec -T mysql mysqladmin ping -h "localhost" --silent 2>/dev/null; do
    sleep 1
done
echo "✅ MySQL prêt !"

# Installer les dépendances si pas déjà fait
if [ ! -d "vendor" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install --no-interaction
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Installation des dépendances NPM..."
    npm install
fi

# Configuration .env
if [ ! -f ".env" ]; then
    echo "⚙️  Configuration de l'environnement..."
    cp .env.example .env
    php artisan key:generate --force
fi

# Migrations et seeders
echo "🗄️  Configuration de la base de données..."
php artisan migrate --force --seed

# Lien symbolique storage
echo "🔗 Création du lien symbolique storage..."
php artisan storage:link

# Compiler les assets
echo "🎨 Compilation des assets..."
npm run build

echo ""
echo "✅ DispoDialyse est prêt !"
echo ""
echo "📍 Accès application : http://localhost:8000"
echo "📍 phpMyAdmin : http://localhost:8080"
echo ""
echo "👤 Comptes de test :"
echo "   Admin : admin@dispodialyse.fr / Password123!"
echo "   Médecin : medecin@dispodialyse.fr / Password123!"
echo "   Infirmier : infirmier@dispodialyse.fr / Password123!"
echo ""