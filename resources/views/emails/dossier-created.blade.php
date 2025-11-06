@component('mail::message')
# Nouveau Dossier Créé

Bonjour {{ $client_name }},

Votre dossier a été créé avec succès.

**Référence:** {{ $dossier_reference }}  
**Titre:** {{ $dossier_title }}

Vous pouvez suivre l'évolution de votre dossier en vous connectant à votre espace client.

@component('mail::button', ['url' => config('app.url') . '/dossiers'])
Voir mon dossier
@endcomponent

Merci de votre confiance,<br>
{{ config('app.name') }}
@endcomponent
