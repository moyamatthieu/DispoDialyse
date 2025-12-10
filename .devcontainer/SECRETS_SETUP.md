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
| `OPENROUTER_API_KEY` | Votre clé API OpenRouter (recommandé pour Roo Code) |
| `ANTHROPIC_API_KEY` | Votre clé API Anthropic (Claude) |
| `OPENAI_API_KEY` | Votre clé API OpenAI |
| `GOOGLE_AI_API_KEY` | Votre clé API Google AI |
| `QDRANT_API_KEY` | Votre clé API Qdrant (base de données vectorielle) |
| `MISTRAL_API_KEY` | Votre clé API Mistral (optionnel) |
| `GROQ_API_KEY` | Votre clé API Groq (optionnel) |
| `DEEPINFRA_API_KEY` | Votre clé API DeepInfra (optionnel) |

### Méthode 2 : Secrets au niveau de l'utilisateur (Pour tous vos Codespaces)

1. Allez sur : `https://github.com/settings/codespaces`
2. Faites défiler jusqu'à **Codespaces secrets**
3. Cliquez sur **New secret**
4. Ajoutez vos secrets et sélectionnez les repositories qui peuvent y accéder

## 🎯 Utilisation dans Roo Code

Les secrets GitHub Codespaces sont disponibles comme variables d'environnement, mais **Roo Code nécessite une configuration manuelle** :

### Étape 1 : Vérifier que vos secrets sont chargés

Dans le terminal :

```bash
echo $OPENROUTER_API_KEY
echo $ANTHROPIC_API_KEY
echo $QDRANT_API_KEY
```

### Étape 2 : Configurer Roo Code

1. **Ouvrez Roo Code** (icône dans la barre latérale de VS Code)
2. Cliquez sur l'icône **⚙️ Settings**
3. Sélectionnez **"OpenRouter"** dans "API Provider"
4. **Copiez-collez** la valeur de `$OPENROUTER_API_KEY` dans le champ "OpenRouter API Key"
5. Sélectionnez votre modèle préféré

**Astuce** : Pour copier facilement votre clé depuis le terminal :
```bash
echo $OPENROUTER_API_KEY | pbcopy  # Sur macOS
echo $OPENROUTER_API_KEY | xclip -selection clipboard  # Sur Linux
```

⚠️ **Important** : 
- Roo Code stocke les clés dans VS Code Secrets (chiffré)
- Cette configuration **persiste** sur la même machine Codespace
- Sur un **nouveau Codespace**, vous devrez reconfigurer Roo Code (mais vos secrets GitHub seront déjà chargés)
- Ne commitez JAMAIS vos clés API dans Git !

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
