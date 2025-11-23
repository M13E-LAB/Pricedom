# 📊 SCHÉMAS VISUELS POUR PRÉSENTATION
## Diagrammes à projeter ou dessiner au tableau

---

## 🔄 SCHÉMA 1 : FLOW COMPLET DU SCAN (Simple)

```
┌─────────┐      ┌─────────┐      ┌─────────┐      ┌─────────┐      ┌─────────┐
│         │      │         │      │         │      │         │      │         │
│  USER   │─────▶│ LARAVEL │─────▶│ PYTHON  │─────▶│ OPENAI  │─────▶│   DB    │
│ (Photo) │      │  (PHP)  │      │ (OCR)   │      │ (GPT-4) │      │(SQLite) │
│         │      │         │      │         │      │         │      │         │
└─────────┘      └─────────┘      └─────────┘      └─────────┘      └─────────┘
    1️⃣              2️⃣              3️⃣              4️⃣              5️⃣
  Upload         Reçoit         Encode          Analyse         Sauvegarde
  ticket         image          base64          image           produits
```

**Explication en 1 phrase :**
> "L'utilisateur upload un ticket → Laravel le stocke et appelle Python → Python envoie l'image à OpenAI → GPT-4 extrait les produits → Laravel sauvegarde en base"

---

## 🏗️ SCHÉMA 2 : ARCHITECTURE 3-TIERS

```
┌────────────────────────────────────────────────────────────────┐
│                        COUCHE PRÉSENTATION                      │
│                                                                 │
│   ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌─────────┐ │
│   │  Scan    │    │Dashboard │    │  Social  │    │ Profile │ │
│   │ Ticket   │    │  Prix    │    │   Feed   │    │  User   │ │
│   └──────────┘    └──────────┘    └──────────┘    └─────────┘ │
│                                                                 │
│        Blade Templates + Tailwind CSS + JavaScript              │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            │ HTTP (POST/GET)
                            │
┌───────────────────────────▼─────────────────────────────────────┐
│                        COUCHE MÉTIER                            │
│                                                                 │
│   ┌──────────────────┐    ┌──────────────────┐                │
│   │   Controllers    │    │     Models       │                │
│   │  - Contribute    │    │  - Contribution  │                │
│   │  - Dashboard     │    │  - User          │                │
│   │  - Social        │    │  - Post          │                │
│   └──────────────────┘    └──────────────────┘                │
│                                                                 │
│   ┌──────────────────────────────────────────┐                │
│   │       Services Externes                  │                │
│   │  ┌────────────┐      ┌────────────┐     │                │
│   │  │   Python   │      │  OpenAI    │     │                │
│   │  │   Script   │─────▶│    API     │     │                │
│   │  └────────────┘      └────────────┘     │                │
│   └──────────────────────────────────────────┘                │
│                                                                 │
│                    Laravel 11 + PHP 8.2                         │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            │ SQL (Eloquent ORM)
                            │
┌───────────────────────────▼─────────────────────────────────────┐
│                        COUCHE DONNÉES                           │
│                                                                 │
│   ┌──────────────────────────────────────────────────────┐    │
│   │              SQLite / PostgreSQL                     │    │
│   │                                                      │    │
│   │  ┌──────┐  ┌──────────────┐  ┌──────┐  ┌────────┐ │    │
│   │  │users │  │contributions │  │posts │  │comments│ │    │
│   │  └──────┘  └──────────────┘  └──────┘  └────────┘ │    │
│   │                                                      │    │
│   └──────────────────────────────────────────────────────┘    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔐 SCHÉMA 3 : FLUX DE SÉCURITÉ

```
┌─────────────────────────────────────────────────────────────────┐
│                    MESURES DE SÉCURITÉ                          │
└─────────────────────────────────────────────────────────────────┘

1️⃣ AUTHENTIFICATION
   ┌──────────┐
   │  LOGIN   │ ──▶ Hash Password (bcrypt)
   └──────────┘     Session Laravel (secure cookie)

2️⃣ CSRF PROTECTION
   ┌──────────┐
   │  FORM    │ ──▶ Token CSRF (dans chaque requête)
   └──────────┘     Validation côté serveur

3️⃣ UPLOAD VALIDATION
   ┌──────────┐
   │  IMAGE   │ ──▶ Type : jpeg, png uniquement
   └──────────┘     Taille : max 10 MB
                    MIME type check

4️⃣ INJECTION PREVENTION
   ┌──────────┐
   │ SHELL    │ ──▶ escapeshellarg() sur tous les arguments
   └──────────┘     Pas d'exécution directe de code user

5️⃣ API KEY PROTECTION
   ┌──────────┐
   │ .env     │ ──▶ Clé API dans .env (pas de commit Git)
   └──────────┘     putenv() pour passer au Python
                    .gitignore contient .env

6️⃣ DATABASE SECURITY
   ┌──────────┐
   │  QUERY   │ ──▶ Eloquent ORM (parameterized queries)
   └──────────┘     Pas de SQL brut
                    Protection injection SQL
```

---

## 📊 SCHÉMA 4 : MODÈLE DE DONNÉES (ERD Simplifié)

```
        ┌─────────────────────┐
        │       USERS         │
        ├─────────────────────┤
        │ 🔑 id               │
        │ 📧 email            │
        │ 🔒 password         │
        │ 👤 name             │
        │ 📅 created_at       │
        └──────────┬──────────┘
                   │
                   │ 1:N
         ┌─────────┴──────────┐
         │                    │
         ▼                    ▼
┌──────────────────┐   ┌──────────────────┐
│  CONTRIBUTIONS   │   │      POSTS       │
├──────────────────┤   ├──────────────────┤
│ 🔑 id            │   │ 🔑 id            │
│ 🔗 user_id (FK)  │   │ 🔗 user_id (FK)  │
│ 🏷️ product_name  │   │ 🖼️ image_path    │
│ 💰 price         │   │ 📝 description   │
│ 🔢 quantity      │   │ 🍎 nutrition_    │
│ 🏪 store_name    │   │    analysis      │
│ 📦 category      │   │ 📅 created_at    │
│ 📷 contribution_ │   └─────────┬────────┘
│    type (scan)   │             │
│ 📅 created_at    │             │ 1:N
└──────────────────┘             │
                          ┌──────┴─────┐
                          │            │
                          ▼            ▼
                  ┌─────────────┐  ┌─────────────┐
                  │  COMMENTS   │  │    LIKES    │
                  ├─────────────┤  ├─────────────┤
                  │ 🔑 id       │  │ 🔑 id       │
                  │ 🔗 post_id  │  │ 🔗 post_id  │
                  │ 🔗 user_id  │  │ 🔗 user_id  │
                  │ 💬 content  │  │ 📅 created  │
                  └─────────────┘  └─────────────┘

LÉGENDE :
🔑 = Clé primaire
🔗 = Clé étrangère
📧 = Email
💰 = Prix
📅 = Date
```

---

## ⚡ SCHÉMA 5 : TIMELINE D'UNE REQUÊTE (Timing)

```
┌──────────────────────────────────────────────────────────────────┐
│              TIMELINE : SCAN D'UN TICKET                         │
└──────────────────────────────────────────────────────────────────┘

0s ────────────────────────────────────────────────────────────▶ 10s

│                                                               │
│ 📸                                                           ✅│
│User                                                      Résultat│
│Upload                                                   Affiché │
│                                                                 │
│                                                                 │
├──────┬──────────┬─────────────────────────────┬────────┬──────┤
│      │          │                             │        │      │
│ 0.5s │   1s     │          6-8s               │  0.5s  │ 0.5s │
│      │          │                             │        │      │
▼      ▼          ▼                             ▼        ▼      ▼

Upload  Stockage  Appel                      Traitement Affichage
image   Laravel   Python → OpenAI API        réponse    frontend
                  + Encode base64            + parse
                  + HTTP Request             JSON
                  + Attente réponse

┌────────────────────────────────────────────────────────────────┐
│                    RÉPARTITION DU TEMPS                        │
├────────────────────────────────────────────────────────────────┤
│ Upload + validation       : 0.5s  (5%)                         │
│ Stockage fichier          : 1s    (10%)                        │
│ Appel OpenAI API          : 6-8s  (70%)  ◄─ GOULOT            │
│ Traitement réponse        : 0.5s  (5%)                         │
│ Affichage frontend        : 0.5s  (5%)                         │
│ ─────────────────────────────────────────                      │
│ TOTAL                     : 8-10s (100%)                       │
└────────────────────────────────────────────────────────────────┘

OPTIMISATIONS POSSIBLES :
💡 Queue jobs (traitement asynchrone)
💡 Caching des résultats similaires
💡 Compression image côté client
💡 WebSockets pour feedback temps réel
```

---

## 🎯 SCHÉMA 6 : DASHBOARD - SOURCES DE DONNÉES

```
┌────────────────────────────────────────────────────────────────┐
│                    DASHBOARD CONTROLLER                        │
└────────────────────────────────────────────────────────────────┘
                              │
                              │ SQL Queries
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌──────────────┐      ┌──────────────┐     ┌──────────────┐
│   COUNT(*)   │      │   AVG(price) │     │  SUM(price)  │
│              │      │              │     │              │
│   Total:     │      │  Prix Moyen: │     │   Valeur:    │
│     1,247    │      │    3.45€     │     │   4,302€     │
└──────────────┘      └──────────────┘     └──────────────┘

        │                     │                     │
        └─────────────────────┼─────────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │   BLADE VIEWS    │
                    │                  │
                    │  📊 Charts.js    │
                    │  🎨 Tailwind CSS │
                    └──────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │  USER BROWSER    │
                    │                  │
                    │  📈 Graphiques   │
                    │  📊 Statistiques │
                    │  🏆 Classements  │
                    └──────────────────┘

REQUÊTES SQL PRINCIPALES :

1️⃣ Statistiques globales
   SELECT COUNT(*) as total,
          COUNT(DISTINCT user_id) as users,
          AVG(price) as avg_price,
          SUM(price * quantity) as total_value
   FROM contributions

2️⃣ Top produits
   SELECT product_name, 
          COUNT(*) as count,
          AVG(price) as avg_price
   FROM contributions
   GROUP BY product_name
   ORDER BY count DESC
   LIMIT 10

3️⃣ Évolution temporelle
   SELECT DATE(created_at) as date,
          COUNT(*) as count
   FROM contributions
   WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
   GROUP BY date
   ORDER BY date ASC
```

---

## 🔄 SCHÉMA 7 : CYCLE DE VIE D'UNE CONTRIBUTION

```
┌────────────────────────────────────────────────────────────────┐
│                CYCLE DE VIE D'UNE CONTRIBUTION                 │
└────────────────────────────────────────────────────────────────┘

    📸 Upload Ticket
         │
         ▼
    ┌──────────┐
    │  PENDING │  ◄─── Stockage temporaire
    └────┬─────┘
         │
         │ Envoi à OpenAI
         │
         ▼
    ┌──────────┐
    │ANALYZING │  ◄─── Traitement IA en cours
    └────┬─────┘
         │
         │ Résultat reçu
         │
         ▼
    ┌──────────┐
    │ EXTRACTED│  ◄─── Données extraites (JSON)
    └────┬─────┘
         │
         │ Validation user
         │
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌────────┐
│ SAVED  │ │REJECTED│  ◄─── User peut refuser
└───┬────┘ └────────┘
    │
    │ Enregistrement DB
    │
    ▼
┌──────────┐
│COMPLETED │  ◄─── Contribution finalisée
└────┬─────┘       • Visible dans le dashboard
     │              • Comptabilisée dans les stats
     │              • Searchable
     │
     ▼
┌──────────┐
│ VERIFIED │  ◄─── (Optionnel) Admin vérifie
└──────────┘

ÉTATS POSSIBLES :
✅ COMPLETED : Contribution enregistrée
⏳ PENDING   : En attente de traitement
🔄 ANALYZING : Analyse IA en cours
❌ REJECTED  : Refusé par l'utilisateur
⚠️ ERROR     : Erreur lors du traitement
✔️ VERIFIED  : Validé par un admin
```

---

## 🌐 SCHÉMA 8 : ARCHITECTURE DÉPLOIEMENT (Production)

```
┌────────────────────────────────────────────────────────────────┐
│                         INTERNET                               │
└───────────────────────────┬────────────────────────────────────┘
                            │
                            │ HTTPS
                            │
┌───────────────────────────▼────────────────────────────────────┐
│                    RAILWAY.APP (PaaS)                          │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │                 Laravel Application                      │ │
│  │  ┌────────────┐  ┌────────────┐  ┌────────────┐        │ │
│  │  │    Web     │  │   Worker   │  │   Cron     │        │ │
│  │  │   Server   │  │  (Queue)   │  │  (Jobs)    │        │ │
│  │  └────────────┘  └────────────┘  └────────────┘        │ │
│  └──────────────────────┬───────────────────────────────────┘ │
│                         │                                      │
│  ┌──────────────────────▼───────────────────────────────────┐ │
│  │               PostgreSQL Database                        │ │
│  │  • users                                                 │ │
│  │  • contributions                                         │ │
│  │  • posts                                                 │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                            │
                            │ API Calls
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
┌──────────────┐   ┌──────────────┐   ┌──────────────┐
│  OpenAI API  │   │ Cloudflare   │   │   Sentry     │
│  (GPT-4o)    │   │ R2 (Storage) │   │(Error Track) │
│              │   │              │   │              │
│ • Vision OCR │   │ • Images     │   │ • Logs       │
│ • Nutrition  │   │ • Tickets    │   │ • Monitoring │
└──────────────┘   └──────────────┘   └──────────────┘

ENVIRONNEMENTS :

┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│ DEVELOPMENT │       │   STAGING   │       │ PRODUCTION  │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ • SQLite    │  ───▶ │ • PostgreSQL│  ───▶ │ • PostgreSQL│
│ • Local     │       │ • Railway   │       │ • Railway   │
│ • Debug ON  │       │ • Testing   │       │ • Debug OFF │
└─────────────┘       └─────────────┘       └─────────────┘
```

---

## 📱 SCHÉMA 9 : RESPONSIVE DESIGN

```
┌────────────────────────────────────────────────────────────────┐
│                    RESPONSIVE BREAKPOINTS                      │
└────────────────────────────────────────────────────────────────┘

📱 MOBILE (< 768px)
┌─────────────┐
│   ZYMA      │  ─── Header compact
├─────────────┤
│   📸        │  ─── Bouton scan plein écran
│  Scanner    │
├─────────────┤
│ [Produit 1] │  ─── Liste verticale
│ [Produit 2] │
│ [Produit 3] │
├─────────────┤
│  📊 Stats   │  ─── Stats empilées
│   (1 col)   │
└─────────────┘


💻 TABLET (768px - 1024px)
┌─────────────────────────────┐
│  ZYMA    🍽️ 📊 👤          │  ─── Navigation complète
├─────────────────────────────┤
│  ┌───────┐   ┌───────┐    │  ─── Grille 2 colonnes
│  │ Scan  │   │ Scan  │    │
│  │ Auto  │   │ Manuel│    │
│  └───────┘   └───────┘    │
├─────────────────────────────┤
│  📊 Stats    📊 Stats      │  ─── Stats 2x2
│  📊 Stats    📊 Stats      │
└─────────────────────────────┘


🖥️ DESKTOP (> 1024px)
┌──────────────────────────────────────────────────────────┐
│  ZYMA      🍽️ Communauté  💸 Contribuer  📊 Dashboard │  ─── Nav complète
├──────────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐ │  ─── 4 colonnes
│  │  📊      │  │  📊      │  │  📊      │  │  📊    │ │
│  │ Total    │  │ Users    │  │  Avg     │  │ Value  │ │
│  └──────────┘  └──────────┘  └──────────┘  └────────┘ │
├──────────────────────────────────────────────────────────┤
│  ┌────────────────────┐    ┌──────────────────────┐   │
│  │  📈 Graphique      │    │  🍩 Donut Chart      │   │  ─── 2 graphiques
│  │  (Ligne)           │    │  (Types)             │   │
│  └────────────────────┘    └──────────────────────┘   │
├──────────────────────────────────────────────────────────┤
│  📋 Tableau des contributions récentes                  │  ─── Table large
└──────────────────────────────────────────────────────────┘
```

---

## 🎨 SCHÉMA 10 : PALETTE DE COULEURS & DESIGN SYSTEM

```
┌────────────────────────────────────────────────────────────────┐
│                      DESIGN SYSTEM ZYMA                        │
└────────────────────────────────────────────────────────────────┘

COULEURS PRINCIPALES :
┌──────────────────┬──────────────────┬──────────────────┐
│   🟠 Orange      │   🔵 Blue        │   🟣 Purple      │
│   Primary        │   Secondary      │   Accent         │
│   #fb923c        │   #3b82f6        │   #a855f7        │
└──────────────────┴──────────────────┴──────────────────┘

GRADIENT DE FOND :
from-slate-900 → via-blue-900 → to-indigo-900

COMPOSANTS :

1️⃣ CARTE (Card)
   ┌─────────────────────────────────┐
   │ bg-black/40                     │  ◄─── Fond translucide
   │ backdrop-blur-lg                │  ◄─── Effet glassmorphism
   │ border border-white/10          │  ◄─── Bordure subtile
   │ rounded-2xl                     │  ◄─── Coins arrondis
   │ p-6                             │  ◄─── Padding
   └─────────────────────────────────┘

2️⃣ BOUTON (Button)
   ┌─────────────────────────────────┐
   │ bg-gradient-to-r                │  ◄─── Gradient horizontal
   │ from-orange-500                 │
   │ to-pink-500                     │
   │ hover:from-orange-600           │  ◄─── Hover effect
   │ text-white                      │
   │ px-6 py-3                       │
   │ rounded-lg                      │
   │ transition-all                  │  ◄─── Animation smooth
   │ transform hover:scale-105       │  ◄─── Zoom au survol
   └─────────────────────────────────┘

3️⃣ STATISTIQUE (Stat Card)
   ┌─────────────────────────────────┐
   │ bg-gradient-to-br               │  ◄─── Gradient diagonal
   │ from-orange-500/20              │  ◄─── Transparence 20%
   │ to-pink-500/20                  │
   │                                 │
   │  📊  Total Contributions        │  ◄─── Icône + titre
   │      1,247                      │  ◄─── Valeur (text-3xl)
   └─────────────────────────────────┘

TYPOGRAPHIE :
┌────────────────────────────────────────┐
│ Titres    : font-bold text-4xl        │
│ Sous-titre: text-xl font-medium       │
│ Body      : text-base                 │
│ Caption   : text-sm text-white/60     │
└────────────────────────────────────────┘

ESPACEMENTS :
┌────────────────────────────────────────┐
│ Container : mx-auto px-4              │
│ Section   : mb-8                      │
│ Card      : p-6                       │
│ Grid Gap  : gap-6                     │
└────────────────────────────────────────┘
```

---

## ✅ CHECKLIST TECHNIQUE À MENTIONNER

```
🔒 SÉCURITÉ
  ✅ CSRF Protection
  ✅ Hash passwords (bcrypt)
  ✅ escapeshellarg() pour shell_exec
  ✅ Validation fichiers uploadés
  ✅ API keys dans .env (pas de commit)
  ✅ Sanitization des entrées user

⚡ PERFORMANCE
  ✅ Compression images
  ✅ Lazy loading
  ✅ Caching Laravel
  ✅ Indexation DB
  ✅ Optimisation requêtes (eager loading)

♿ ACCESSIBILITÉ
  ✅ Contraste texte/fond > 4.5:1
  ✅ Alt text sur images
  ✅ Navigation au clavier
  ✅ Focus visible
  ✅ Labels sur formulaires

📱 RESPONSIVE
  ✅ Mobile-first design
  ✅ Breakpoints Tailwind
  ✅ Touch-friendly (boutons > 44px)
  ✅ Viewport meta tag

🧪 TESTS (À venir)
  ⏳ Unit tests (PHPUnit)
  ⏳ Feature tests (Laravel Dusk)
  ⏳ API tests (Postman)

📊 MONITORING (Production)
  ✅ Logs Laravel
  ⏳ Sentry (error tracking)
  ⏳ Analytics (Google Analytics)
  ⏳ Uptime monitoring
```

---

## 🎤 PHRASES CLÉS POUR LA PRÉSENTATION

```
💡 ACCROCHE :
"Zyma transforme un ticket de caisse en données exploitables en moins de 10 secondes grâce à l'IA"

💡 ARCHITECTURE :
"J'ai choisi une architecture modulaire avec Laravel pour le web et Python pour l'IA, communiquant via shell_exec et JSON"

💡 IA :
"J'utilise GPT-4o-mini d'OpenAI, un modèle multimodal capable de comprendre à la fois du texte et des images"

💡 PERFORMANCE :
"Le goulot d'étranglement se situe au niveau de l'API OpenAI (6-8s), mais je peux optimiser avec des queue jobs asynchrones"

💡 SÉCURITÉ :
"La sécurité est assurée à plusieurs niveaux : CSRF tokens, validation des uploads, escapeshellarg, et API keys dans .env"

💡 UX :
"L'interface utilise le glassmorphism avec Tailwind CSS pour un design moderne, et Chart.js pour des graphiques interactifs"

💡 SCALABILITÉ :
"L'architecture actuelle peut facilement évoluer vers des microservices avec Redis pour le caching et des workers dédiés"
```

---

**Tous ces schémas sont prêts à être utilisés dans ta présentation !** 🚀

Tu peux :
1. Les projeter directement (fichier Markdown)
2. Les convertir en slides PowerPoint
3. Les dessiner au tableau blanc pendant ta présentation
4. Les imprimer en support papier

Bonne présentation ! 💪

