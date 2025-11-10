<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation ELI VOYAGES</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7fafc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .header img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
            color: #2d3748;
            line-height: 1.6;
        }
        .content h2 {
            color: #1a202c;
            margin-top: 0;
        }
        .client-code {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .client-code strong {
            color: #d97706;
            font-size: 18px;
        }
        .cta-button {
            display: inline-block;
            background: #d97706;
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 25px 0;
            transition: background 0.3s;
        }
        .cta-button:hover {
            background: #b45309;
        }
        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 25px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #d97706;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌍 ELI VOYAGES</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Votre partenaire immigration</p>
        </div>

        <div class="content">
            <h2>Bonjour {{ $prenom }} {{ $nom }},</h2>

            <p>Nous sommes ravis de vous accueillir ! Notre équipe vous invite à créer votre compte personnel sur la plateforme <strong>ELI VOYAGES Connect</strong>.</p>

            <div class="client-code">
                <p style="margin: 0 0 8px 0; font-size: 14px;">Votre code client unique :</p>
                <strong>{{ $clientCode }}</strong>
            </div>

            <p>Ce code vous permettra de suivre l'avancement de votre dossier, de télécharger vos documents, et de communiquer avec notre équipe de consultants.</p>

            <div style="text-align: center;">
                <a href="{{ $acceptUrl }}" class="cta-button">
                    Créer mon compte
                </a>
            </div>

            <div class="info-box">
                <p style="margin: 0 0 10px 0;"><strong>📋 Ce que vous pourrez faire :</strong></p>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Visualiser le statut de votre dossier en temps réel</li>
                    <li>Télécharger et signer vos contrats en ligne</li>
                    <li>Uploader vos documents nécessaires</li>
                    <li>Prendre rendez-vous avec nos consultants</li>
                    <li>Recevoir des notifications importantes</li>
                </ul>
            </div>

            <p><strong>⏰ Cette invitation expire le {{ $expiresAt }}.</strong></p>

            <p>Si vous n'êtes pas à l'origine de cette demande ou si vous avez des questions, n'hésitez pas à nous contacter.</p>

            <p style="margin-top: 30px;">
                Cordialement,<br>
                <strong>L'équipe ELI VOYAGES</strong>
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                <strong>ELI VOYAGES SARL U</strong><br>
                Votre succès, notre mission.
            </p>
            <p style="margin: 0;">
                📧 <a href="mailto:info@eli-voyages.com">info@eli-voyages.com</a> |
                🌐 <a href="https://eli-voyages.com">www.eli-voyages.com</a>
            </p>
            <p style="margin: 15px 0 0 0; color: #9ca3af; font-size: 12px;">
                © {{ date('Y') }} ELI VOYAGES SARL U. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
