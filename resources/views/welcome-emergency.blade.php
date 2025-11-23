<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 ZYMA - Mode Urgence</title>
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e, #16213e, #2d3a8c);
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            max-width: 600px;
        }
        h1 {
            font-size: 3em;
            margin-bottom: 20px;
            color: #ff6b35;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.2em;
            transition: transform 0.3s;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .btn-debug {
            background: linear-gradient(45deg, #00ff00, #32cd32);
        }
        .btn-emergency {
            background: linear-gradient(45deg, #ff0000, #ff4500);
            font-size: 1.5em;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .status {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .instructions {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 ZYMA - MODE URGENCE</h1>
        
        <div class="status">
            <h2>🔧 État du Système</h2>
            <p>Les fonctions de connexion normales sont temporairement indisponibles.</p>
            <p>Utilisez les boutons ci-dessous pour accéder à la plateforme.</p>
        </div>

        <div class="instructions">
            <h3>📋 Instructions :</h3>
            <ol>
                <li><strong>DEBUG</strong> : Vérifie et répare la base de données</li>
                <li><strong>CONNEXION D'URGENCE</strong> : Te connecte automatiquement</li>
                <li>Si ça ne marche pas, actualise la page dans 2-3 minutes</li>
            </ol>
        </div>

        <div style="margin: 30px 0;">
            <a href="/debug-fix" class="btn btn-debug">
                🔧 DEBUG & RÉPARATION
            </a>
            
            <a href="/emergency-login" class="btn btn-emergency">
                🚨 CONNEXION D'URGENCE
            </a>
            
            <a href="/login" class="btn">
                🔑 Connexion Normale
            </a>
            
            <a href="/register" class="btn">
                🚀 Inscription
            </a>
        </div>

        <div class="status">
            <h3>👤 Comptes de Test</h3>
            <p><strong>Email :</strong> admin@zyma.com</p>
            <p><strong>Password :</strong> password123</p>
            <hr style="margin: 10px 0; opacity: 0.3;">
            <p><strong>Email :</strong> test@test.com</p>
            <p><strong>Password :</strong> test123</p>
        </div>

        <div style="margin-top: 30px; opacity: 0.7; font-size: 0.9em;">
            <p>🔄 Page mise à jour automatiquement toutes les 30 secondes</p>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html> 