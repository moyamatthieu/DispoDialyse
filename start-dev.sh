#!/bin/bash
echo "🚀 Démarrage de DispoDialyse en mode développement..."

# Arrêter les processus existants
pkill -f "artisan serve" 2>/dev/null
pkill -f "vite" 2>/dev/null
sleep 2

# Démarrer Laravel
echo "📦 Démarrage du serveur Laravel..."
php artisan serve --host=0.0.0.0 --port=8000 &

# Démarrer Vite
echo "⚡ Démarrage de Vite..."
npx vite &

sleep 3

echo ""
echo "✅ Serveurs démarrés !"
echo ""
echo "🌐 Application: https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
echo "⚡ Vite HMR: https://${CODESPACE_NAME}-5173.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
echo ""
echo "👤 Identifiants de test:"
echo "   Email: admin@dispodialyse.fr"
echo "   Mot de passe: password"
echo ""
