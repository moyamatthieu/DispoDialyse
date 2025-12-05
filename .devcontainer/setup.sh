#!/bin/bash

echo "🚀 Configuration de l'environnement DispoDialyse..."

# Créer le fichier .env s'il n'existe pas
if [ ! -f .env ]; then
    echo "📝 Création du fichier .env à partir du template..."
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "✅ Fichier .env créé. N'oubliez pas de configurer vos secrets GitHub Codespaces!"
    fi
fi

# Installation des dépendances Python si requirements.txt existe
if [ -f requirements.txt ]; then
    echo "📦 Installation des dépendances Python..."
    pip install -r requirements.txt
fi

# Installation des dépendances Node.js si package.json existe
if [ -f package.json ]; then
    echo "📦 Installation des dépendances Node.js..."
    npm install
fi

echo "✨ Configuration terminée!"
