@component('mail::message')
# Signature de Contrat Requise

Bonjour {{ $signer_name }},

Votre signature est requise pour le contrat du dossier **{{ $dossier_reference }}**.

Veuillez cliquer sur le bouton ci-dessous pour accéder au document et le signer électroniquement.

@component('mail::button', ['url' => $signature_url])
Signer le contrat
@endcomponent

Ce lien est sécurisé et valide pendant 7 jours.

Si vous avez des questions, n'hésitez pas à nous contacter.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
