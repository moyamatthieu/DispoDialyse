# Guide de configuration des secrets GitHub Codespaces

## 🔐 Configuration des secrets (Recommandé)

Pour que vos clés API soient automatiquement disponibles sur toutes vos machines sans avoir à les ressaisir :

### Méthode 1 : Secrets au niveau du Repository (Recommandé)

1. Allez sur votre repository GitHub : `https://github.com/moyamatthieu/DispoDialyse`
2. Cliquez sur **Settings** (⚙️)
3. Dans le menu de gauche, allez dans **Secrets and variables** → **Codespaces**
4. Cliquez sur **New repository secret**
5. Ajoutez vos secrets un par un :

| Nom du secret | Description |
|---------------|-------------|
| `OPENAI_API_KEY` | Votre clé API OpenAI |
| `ANTHROPIC_API_KEY` | Votre clé API Anthropic (Claude) |
| `GOOGLE_AI_API_KEY` | Votre clé API Google AI |
| `MISTRAL_API_KEY` | Votre clé API Mistral (optionnel) |
| `GROQ_API_KEY` | Votre clé API Groq (optionnel) |

### Méthode 2 : Secrets au niveau de l'utilisateur (Pour tous vos Codespaces)

1. Allez sur : `https://github.com/settings/codespaces`
2. Faites défiler jusqu'à **Codespaces secrets**
3. Cliquez sur **New secret**
4. Ajoutez vos secrets et sélectionnez les repositories qui peuvent y accéder

## 🎯 Utilisation dans Roo Code

Une fois configurés, vos secrets seront automatiquement disponibles comme variables d'environnement dans votre Codespace. Roo Code pourra les utiliser directement.

### Vérification

Pour vérifier que vos secrets sont bien chargés, dans le terminal :

```bash
echo $OPENAI_API_KEY
echo $ANTHROPIC_API_KEY
```

⚠️ **Important** : Ne commitez JAMAIS vos clés API dans Git !

## 🔄 Méthode alternative : Fichier .env local (Non persistant)

Si vous ne voulez pas utiliser les secrets GitHub, vous pouvez créer un fichier `.env` :

```bash
cp .env.example .env
# Puis éditez .env avec vos vraies clés
```

⚠️ Cette méthode nécessite de recréer le fichier `.env` sur chaque nouvelle machine.

## 🛠️ Configuration de Roo Code

Roo Code devrait automatiquement détecter vos clés API depuis les variables d'environnement. Si nécessaire :

1. Ouvrez les paramètres de Roo Code (Ctrl+Shift+P → "Roo Code: Settings")
2. Les clés API seront automatiquement récupérées depuis les variables d'environnement

## 📚 Ressources

- [Documentation GitHub Codespaces Secrets](https://docs.github.com/en/codespaces/managing-your-codespaces/managing-encrypted-secrets-for-your-codespaces)
- [Documentation Dev Container](https://containers.dev/)
