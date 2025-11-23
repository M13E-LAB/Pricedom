# 🤗 Configuration Hugging Face pour l'analyse de tickets

## ✅ Ce qui a été fait :

L'application a été modifiée pour utiliser **Qwen2.5-VL-7B-Instruct** (Hugging Face) au lieu d'OpenAI pour l'analyse de tickets de caisse.

## 📝 Étapes pour activer la fonctionnalité :

### 1️⃣ Créer un compte Hugging Face (2 min)

1. Allez sur : https://huggingface.co/join
2. Créez votre compte avec votre email
3. Confirmez votre email

### 2️⃣ Créer un token API (1 min)

1. Connectez-vous et allez sur : https://huggingface.co/settings/tokens
2. Cliquez sur **"New token"**
3. Nom du token : `Zyma OCR`
4. Type : **"Read"** (pas besoin de Write)
5. Cliquez sur **"Generate token"**
6. **Copiez le token** (commence par `hf_...`)

### 3️⃣ Configurer le token dans l'application

1. Ouvrez le fichier `.env` à la racine du projet Zyma0.5
2. Ajoutez cette ligne à la fin :

```
HUGGINGFACE_API_KEY=hf_VOTRE_TOKEN_ICI
```

Remplacez `hf_VOTRE_TOKEN_ICI` par votre vrai token.

### 4️⃣ Redémarrer l'application

```bash
# Arrêter les serveurs
pkill -f "php artisan serve"
pkill -f "vite"

# Redémarrer
cd "/Users/mae/Downloads/Gen AI /Zyma0.5"
php artisan serve &
npm run dev &
```

## 🎯 Comment tester :

1. Ouvrez http://localhost:8000
2. Connectez-vous ou créez un compte
3. Allez dans **"Contribuer"** → **"Scanner un ticket"**
4. Uploadez une photo de ticket de caisse
5. Le modèle Qwen2.5-VL analysera automatiquement le ticket !

## 📊 Avantages de Qwen2.5-VL :

- ✅ **Gratuit** (API Hugging Face)
- ✅ **Excellent OCR** (spécialisé pour documents)
- ✅ **7B paramètres** (rapide)
- ✅ **Pas de restriction EU**
- ✅ **Licence Apache 2.0** (open source)

## ⚠️ Limitations :

- **Limite gratuite** : ~1000 requêtes/mois
- **Temps de réponse** : 5-15 secondes (premier appel peut être plus long si le modèle doit se charger)
- Si le modèle est "en veille", il peut mettre 30s à démarrer

## 🚀 Alternative : Ollama local (illimité, plus rapide)

Si vous voulez utiliser le modèle en local (illimité et plus rapide) :

```bash
# Installer Ollama
brew install ollama

# Télécharger Llama Vision
ollama pull llama3.2-vision

# Démarrer Ollama
ollama serve
```

Puis modifiez le code pour pointer vers l'API locale d'Ollama.

---

**Questions ?** Contactez votre développeur ! 😊



