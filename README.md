# DispoDialyse

## 🚀 Démarrage rapide

Ce repository est configuré pour fonctionner avec GitHub Codespaces avec une configuration automatique des clés API.

### ⚙️ Configuration initiale (Une seule fois)

Pour ne plus avoir à ressaisir vos clés API à chaque changement de machine :

1. **Configurez vos secrets GitHub Codespaces** (recommandé)
   - Suivez le guide : [.devcontainer/SECRETS_SETUP.md](.devcontainer/SECRETS_SETUP.md)
   - Vos clés seront automatiquement disponibles dans tous vos Codespaces

2. **Ou utilisez un fichier .env local** (moins pratique)
   ```bash
   cp .env.example .env
   # Éditez .env avec vos clés API
   ```

### 🎯 Utilisation avec Roo Code

Une fois les secrets configurés, Roo Code aura automatiquement accès à vos clés API via les variables d'environnement :
- `OPENAI_API_KEY`
- `ANTHROPIC_API_KEY`
- `GOOGLE_AI_API_KEY`
- etc.

### 📦 Structure du projet

```
DispoDialyse/
├── .devcontainer/          # Configuration du Dev Container
│   ├── devcontainer.json   # Configuration principale
│   ├── setup.sh           # Script d'installation
│   └── SECRETS_SETUP.md   # Guide de configuration des secrets
├── .env.example           # Template des variables d'environnement
├── .gitignore            # Fichiers à ignorer (inclut .env)
└── README.md             # Ce fichier
```

### 🔐 Sécurité

- ✅ Les fichiers `.env` sont ignorés par Git
- ✅ Les secrets GitHub Codespaces sont chiffrés
- ✅ Aucune clé API n'est commitée dans le repository
- ✅ Configuration réutilisable sur toutes vos machines

### 📝 Notes

- Les extensions VS Code nécessaires sont automatiquement installées
- Le script de configuration s'exécute automatiquement au démarrage du Codespace
- Pour plus de détails sur la configuration des secrets : voir [.devcontainer/SECRETS_SETUP.md](.devcontainer/SECRETS_SETUP.md)