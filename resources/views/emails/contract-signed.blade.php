@component('mail::message')
# Contrat Signé avec Succès

Bonjour {{ $recipient_name }},

Nous avons bien reçu votre signature pour le dossier **{{ $dossier_reference }}**.

Votre contrat a été signé avec succès et est maintenant disponible dans votre espace client.

@component('mail::button', ['url' => config('app.url') . '/dossiers'])
Voir le contrat signé
@endcomponent

Merci pour votre confiance.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
